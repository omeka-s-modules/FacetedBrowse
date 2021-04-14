<?php
namespace FacetedBrowse\FacetType;

use Laminas\View\Renderer\PhpRenderer;

class Unknown implements FacetTypeInterface
{
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getLabel() : string
    {
        return '[Unknown]'; // @translate
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        return '';
    }
}
