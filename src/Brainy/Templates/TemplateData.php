<?php
/**
 * @package Brainy
 * @author Matt Basta
 * @author Uwe Tews
 */

namespace Box\Brainy\Templates;

use Box\Brainy\Brainy;


class TemplateData
{
    /**
     * template variables
     *
     * @var array
     * @internal
     */
    public $tpl_vars = array();
    /**
     * Parent template (if any)
     *
     * @var Template
     * @internal
     */
    public $parent = null;

    /**
     * Assigns $value to the variable in $var. If an associative array is
     * passed as the only parameter, it is a mapping of variables to assign to
     * the values to assign to them.
     *
     * @param  array|string         $var the template variable name(s)
     * @param  mixed|null|void      $value   the value to assign
     * @param  int|void             $scope   the scope to associate with the Smarty_Variable instance
     * @return \Box\Brainy\Templates\TemplateData current \Box\Brainy\Templates\TemplateData (or Smarty or Template) instance for chaining
     */
    public function assign($var, $value = null, $scope = -1) {
        if (is_array($var)) {
            foreach ($var as $_key => $_val) {
                if ($_key != '') {
                    $this->assignSingleVar($_key, $_val, $scope);
                }
            }
        } else {
            if ($var != '') {
                $this->assignSingleVar($var, $value, $scope);
            }
        }

        return $this;
    }

    /**
     * Assigns $value to the variale $var.
     *
     * @param  string $var the template variable name
     * @param  mixed $value the value to assign
     * @param  int $scope the scope to associate with the Smarty_Variable
     * @return void
     */
    private function assignSingleVar($var, $value, $scope) {
        if ($scope === -1) {
            $scope = Brainy::$default_assign_scope;
        }

        $variable = new Variable($value, $scope);
        $this->tpl_vars[$var] = $variable;

        if ($scope === Brainy::SCOPE_PARENT) {
            if ($this->parent != null) {
                $this->parent->tpl_vars[$var] = clone $variable;
            }
        } elseif ($scope === Brainy::SCOPE_ROOT || $scope === Brainy::SCOPE_GLOBAL) {
            $pointer = $this->parent;
            while ($pointer != null) {
                $pointer->tpl_vars[$var] = clone $variable;
                $pointer = $pointer->parent;
            }
        }

        if ($scope === Brainy::SCOPE_GLOBAL) {
            Brainy::$global_tpl_vars[$var] = clone $variable;
        }
    }

    /**
     * Assigns a global Smarty variable to the global scope.
     *
     * @param  string               $varname the global variable name
     * @param  mixed                $value   the value to assign
     * @return \Box\Brainy\Templates\TemplateData current \Box\Brainy\Templates\TemplateData (or Smarty or Template) instance for chaining
     * @todo This may not work with multiple Brainy instances.
     */
    public function assignGlobal($varname, $value = null) {
        if ($varname != '') {
            Brainy::$global_tpl_vars[$varname] = new Variable($value);
            $ptr = $this;
            while ($ptr instanceof Template) {
                $ptr->tpl_vars[$varname] = clone Brainy::$global_tpl_vars[$varname];
                $ptr = $ptr->parent;
            }
        }

        return $this;
    }

    /**
     * Returns a single or all assigned template variables
     *
     * @param  string $varname Name of variable to process, or null to return all
     * @param  \Box\Brainy\Templates\TemplateData $_ptr Optional reference to data object
     * @param  boolean $search_parents Whether to include results from parent scopes
     * @return string|array variable value or or array of variables
     */
    public function getTemplateVars($varname = null, $_ptr = null, $search_parents = true) {
        if (isset($varname)) {
            $_var = $this->getVariable($varname, $_ptr, $search_parents, false);
            if (is_object($_var)) {
                return $_var->value;
            } else {
                return null;
            }
        } else {
            $_result = array();
            if ($_ptr === null) {
                $_ptr = $this;
            } while ($_ptr !== null) {
                foreach ($_ptr->tpl_vars AS $key => $var) {
                    if (!array_key_exists($key, $_result)) {
                        $_result[$key] = $var->value;
                    }
                }
                // not found, try at parent
                if ($search_parents) {
                    $_ptr = $_ptr->parent;
                } else {
                    $_ptr = null;
                }
            }
            if ($search_parents && isset(Brainy::$global_tpl_vars)) {
                foreach (Brainy::$global_tpl_vars AS $key => $var) {
                    if (!array_key_exists($key, $_result)) {
                        $_result[$key] = $var->value;
                    }
                }
            }

            return $_result;
        }
    }

    /**
     * Clear the given assigned template variable.
     *
     * @param  string|string[]         $tpl_var The template variable(s) to clear
     * @return \Box\Brainy\Templates\TemplateData current \Box\Brainy\Templates\TemplateData (or Smarty or Template) instance for chaining
     */
    public function clearAssign($tpl_var) {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $curr_var) {
                unset($this->tpl_vars[$curr_var]);
            }
        } else {
            unset($this->tpl_vars[$tpl_var]);
        }

        return $this;
    }

    /**
     * Clear all the assigned template variables.
     * @return \Box\Brainy\Templates\TemplateData current \Box\Brainy\Templates\TemplateData (or Smarty or Template) instance for chaining
     */
    public function clearAllAssign() {
        $this->tpl_vars = array();

        return $this;
    }

    /**
     * Return the contents of an assigned variable.
     *
     * @param  string  $variable       the name of the Smarty variable
     * @param  \Box\Brainy\Templates\TemplateData|null $_ptr Optional reference to the data object
     * @param  boolean $search_parents Whether to search in the parent scope
     * @param  boolean $error_enable Whether to raise an error when the variable is not found.
     * @return mixed The contents of the variable.
     */
    public function getVariable($variable, $_ptr = null, $search_parents = true, $error_enable = true) {
        if ($_ptr === null) {
            $_ptr = $this;
        }
        while ($_ptr !== null) {
            if (isset($_ptr->tpl_vars[$variable])) {
                // found it, return it
                return $_ptr->tpl_vars[$variable];
            }
            // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset(Brainy::$global_tpl_vars[$variable])) {
            // found it, return it
            return Brainy::$global_tpl_vars[$variable];
        }
        if ($this->smarty->error_unassigned && $error_enable) {
            trigger_error('Undefined variable "' . $variable . '"', E_USER_NOTICE);
        }

        return new UndefinedVariable;
    }

}
