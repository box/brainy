<?php

namespace Box\Brainy\Tests\Helpers;


class Data extends \Box\Brainy\Templates\TemplateData
{
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
     * @param Smarty       $smarty  global smarty instance
     */
    public function __construct($_parent = null, $smarty = null) {
        $this->smarty = $smarty;
        if (is_object($_parent)) {
            // when object set up back pointer
            $this->parent = $_parent;
        } elseif (is_array($_parent)) {
            // set up variable values
            foreach ($_parent as $_key => $_val) {
                $this->tpl_vars[$_key] = new Smarty_variable($_val);
            }
        } elseif ($_parent != null) {
            throw new SmartyException("Wrong type for template variables");
        }
    }

}
