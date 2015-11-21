<?php

namespace Box\Brainy\Tests\Helpers;


class Data
{
    use \Box\Brainy\Templates\TemplateData;

    /**
     * Smarty object
     *
     * @var Smarty
     * @internal
     */
    public $smarty = null;

    /**
     * Create Smarty data object
     *
     * @param Smarty|array $_parent parent template
     */
    public function __construct($_parent = null, $smarty = null)
    {
        $this->parent = $_parent;
        $this->smarty = $smarty;
    }

    /**
     * Hook to allow subclasses to initialize their data structures.
     * @return void
     */
    protected function setUpTemplateData()
    {}

}
