<?php
namespace FacetedBrowse;

use Omeka\Module\AbstractModule;
use Laminas\Mvc\MvcEvent;
use Laminas\EventManager\SharedEventManagerInterface;
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
        $acl->allow(null, 'FacetedBrowse\Api\Adapter\FacetedBrowseCategoryAdapter', ['read']);
        $acl->allow(null, 'FacetedBrowse\Entity\FacetedBrowsePage', ['read']);
        $acl->allow(null, 'FacetedBrowse\Entity\FacetedBrowseCategory', ['read']);
    }

    public function install(ServiceLocatorInterface $services)
    {
        $sql = <<<'SQL'
CREATE TABLE faceted_browse_facet (id INT UNSIGNED AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', position INT NOT NULL, INDEX IDX_13E44E7312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_column (id INT UNSIGNED AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', position INT NOT NULL, INDEX IDX_81AF52F612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_page_category (id INT UNSIGNED AUTO_INCREMENT NOT NULL, page_id INT UNSIGNED NOT NULL, category_id INT UNSIGNED NOT NULL, position INT NOT NULL, INDEX IDX_3EAD23ABC4663E4 (page_id), INDEX IDX_3EAD23AB12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_page (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, site_id INT NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_96A2980D7E3C61F9 (owner_id), INDEX IDX_96A2980DF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE faceted_browse_category (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, site_id INT NOT NULL, created DATETIME NOT NULL, modified DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, query LONGTEXT NOT NULL, INDEX IDX_3AFD1257E3C61F9 (owner_id), INDEX IDX_3AFD125F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE faceted_browse_facet ADD CONSTRAINT FK_13E44E7312469DE2 FOREIGN KEY (category_id) REFERENCES faceted_browse_category (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_column ADD CONSTRAINT FK_81AF52F612469DE2 FOREIGN KEY (category_id) REFERENCES faceted_browse_category (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_page_category ADD CONSTRAINT FK_3EAD23ABC4663E4 FOREIGN KEY (page_id) REFERENCES faceted_browse_page (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_page_category ADD CONSTRAINT FK_3EAD23AB12469DE2 FOREIGN KEY (category_id) REFERENCES faceted_browse_category (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_page ADD CONSTRAINT FK_96A2980D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE faceted_browse_page ADD CONSTRAINT FK_96A2980DF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
ALTER TABLE faceted_browse_category ADD CONSTRAINT FK_3AFD1257E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE faceted_browse_category ADD CONSTRAINT FK_3AFD125F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
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
        $conn->exec('DROP TABLE IF EXISTS faceted_browse_page_category;');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
    }
}
