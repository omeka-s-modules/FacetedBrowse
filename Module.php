<?php
namespace FacetedBrowse;

use Composer\Semver\Comparator;
use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include sprintf('%s/config/module.config.php', __DIR__);
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 'FacetedBrowse\Controller\Site\Page');
        $acl->allow(null, 'FacetedBrowse\Api\Adapter\FacetedBrowsePageAdapter', ['read']);
        $acl->allow(null, 'FacetedBrowse\Api\Adapter\FacetedBrowseCategoryAdapter', ['search', 'read']);
        $acl->allow(null, 'FacetedBrowse\Entity\FacetedBrowsePage', ['read']);
        $acl->allow(null, 'FacetedBrowse\Entity\FacetedBrowseCategory', ['read']);
    }

    public function install(ServiceLocatorInterface $services)
    {
        $sql = <<<'SQL'
CREATE TABLE faceted_browse_facet (id INT UNSIGNED AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', position INT NOT NULL, INDEX IDX_13E44E7312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_column (id INT UNSIGNED AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, exclude_sort_by TINYINT(1) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', position INT NOT NULL, INDEX IDX_81AF52F612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_page (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, site_id INT NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, title VARCHAR(255) NOT NULL, resource_type VARCHAR(255) NOT NULL, INDEX IDX_96A2980D7E3C61F9 (owner_id), INDEX IDX_96A2980DF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_category (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, site_id INT NOT NULL, page_id INT UNSIGNED NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, query LONGTEXT NOT NULL, sort_by VARCHAR(255) DEFAULT NULL, sort_order VARCHAR(255) DEFAULT NULL, helper_text LONGTEXT DEFAULT NULL, helper_text_button_label VARCHAR(255) DEFAULT NULL, position INT NOT NULL, INDEX IDX_3AFD1257E3C61F9 (owner_id), INDEX IDX_3AFD125F6BD1646 (site_id), INDEX IDX_3AFD125C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE faceted_browse_facet ADD CONSTRAINT FK_13E44E7312469DE2 FOREIGN KEY (category_id) REFERENCES faceted_browse_category (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_column ADD CONSTRAINT FK_81AF52F612469DE2 FOREIGN KEY (category_id) REFERENCES faceted_browse_category (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_page ADD CONSTRAINT FK_96A2980D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE faceted_browse_page ADD CONSTRAINT FK_96A2980DF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_category ADD CONSTRAINT FK_3AFD1257E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE faceted_browse_category ADD CONSTRAINT FK_3AFD125F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_category ADD CONSTRAINT FK_3AFD125C4663E4 FOREIGN KEY (page_id) REFERENCES faceted_browse_page (id) ON DELETE CASCADE;
SQL;
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec($sql);
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec('DROP TABLE IF EXISTS faceted_browse_page;');
        $conn->exec('DROP TABLE IF EXISTS faceted_browse_category;');
        $conn->exec('DROP TABLE IF EXISTS faceted_browse_facet;');
        $conn->exec('DROP TABLE IF EXISTS faceted_browse_column;');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        if (Comparator::lessThan($oldVersion, '1.3.0')) {
            $conn->exec('ALTER TABLE faceted_browse_column ADD exclude_sort_by TINYINT(1) NOT NULL AFTER type');
        }
        if (Comparator::lessThan($oldVersion, '1.4.0')) {
            $conn->exec('ALTER TABLE faceted_browse_category ADD sort_by VARCHAR(255) DEFAULT NULL AFTER query');
            $conn->exec('ALTER TABLE faceted_browse_category ADD sort_order VARCHAR(255) DEFAULT NULL AFTER sort_by');
        }
        if (Comparator::lessThan($oldVersion, '1.5.0')) {
            $conn->exec('ALTER TABLE faceted_browse_category ADD user_text LONGTEXT DEFAULT NULL');
        }
        if (Comparator::lessThan($oldVersion, '1.5.1')) {
            $conn->exec('ALTER TABLE faceted_browse_category CHANGE user_text helper_text LONGTEXT DEFAULT NULL');
            $conn->exec('ALTER TABLE faceted_browse_category ADD helper_text_button_label VARCHAR(255) DEFAULT NULL AFTER helper_text');
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        // Copy mapping-related data for the CopyResources module.
        $sharedEventManager->attach(
            '*',
            'copy_resources.copy_site',
            function (Event $event) {
                $services = $this->getServiceLocator();
                $api = $services->get('Omeka\ApiManager');

                $site = $event->getParam('resource');
                $siteCopy = $event->getParam('resource_copy');
                $copyResources = $event->getParam('copy_resources');

                $copyResources->revertSiteNavigationLinkTypes($siteCopy->id(), 'facetedBrowse');

                // Copy pages.
                $pages = $api->search('faceted_browse_pages', ['site_id' => $site->id()])->getContent();
                $pageMap = [];
                foreach ($pages as $page) {
                    $callback = function (&$jsonLd) use ($siteCopy){
                        unset($jsonLd['o:owner']);
                        $jsonLd['o:site']['o:id'] = $siteCopy->id();
                    };
                    $pageCopy = $copyResources->createResourceCopy('faceted_browse_pages', $page, $callback);
                    $pageMap[$page->id()] = $pageCopy->id();

                    // Copy categories.
                    $categories = $api->search('faceted_browse_categories', ['page_id' => $page->id()])->getContent();
                    foreach ($categories as $category) {
                        $callback = function (&$jsonLd) use ($siteCopy, $pageCopy){
                            unset($jsonLd['o:owner']);
                            $jsonLd['o:site']['o:id'] = $siteCopy->id();
                            $jsonLd['o-module-faceted_browse:page']['o:id'] = $pageCopy->id();
                        };
                        $copyResources->createResourceCopy('faceted_browse_categories', $category, $callback);
                    }
                }

                // Modify site navigation.
                $callback = function (&$link) use ($pageMap) {
                    if (isset($link['data']['page_id']) && is_numeric($link['data']['page_id'])) {
                        $id = $link['data']['page_id'];
                        $link['data']['page_id'] = array_key_exists($id, $pageMap) ? $pageMap[$id] : $id;
                    }
                };
                $copyResources->modifySiteNavigation($siteCopy->id(), 'facetedBrowse', $callback);

            }
        );
    }
}
