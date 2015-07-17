<?php
/**
 * Smarty Internal Plugin Data
 *
 * This file contains the basic classes and methods for template and variable creation
 *
 * @package Brainy
 * @subpackage Template
 * @author Uwe Tews
 */

/**
 * Base class with template and variable methods
 *
 * @package Brainy
 * @subpackage Template
 */
class Smarty_Internal_Data
{
    /**
     * Name of class used for templates
     *
     * @var string
     * @internal
     */
    public $template_class = 'Smarty_Internal_Template';
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
     * @var Smarty_Internal_Template
     * @internal
     */
    public $parent = null;
    /**
     * configuration settings
     *
     * @var array
     * @internal
     */
    public $config_vars = array();

    /**
     * Assigns $value to the variable in $var. If an associative array is
     * passed as the only parameter, it is a mapping of variables to assign to
     * the values to assign to them.
     *
     * @param  array|string         $var the template variable name(s)
     * @param  mixed|null|void      $value   the value to assign
     * @param  int|void             $scope   the scope to associate with the Smarty_Variable instance
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
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
            $scope = Smarty::$default_assign_scope;
        }

        $variable = new Smarty_variable($value, $scope);
        $this->tpl_vars[$var] = $variable;

        if ($scope === Smarty::SCOPE_PARENT) {
            if ($this->parent != null) {
                $this->parent->tpl_vars[$var] = clone $variable;
            }
        } elseif ($scope === Smarty::SCOPE_ROOT || $scope === Smarty::SCOPE_GLOBAL) {
            $pointer = $this->parent;
            while ($pointer != null) {
                $pointer->tpl_vars[$var] = clone $variable;
                $pointer = $pointer->parent;
            }
        }

        if ($scope === Smarty::SCOPE_GLOBAL) {
            Smarty::$global_tpl_vars[$var] = clone $variable;
        }
    }

    /**
     * Assigns a global Smarty variable to the global scope.
     *
     * @param  string               $varname the global variable name
     * @param  mixed                $value   the value to assign
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
     * @todo This may not work with multiple Brainy instances.
     */
    public function assignGlobal($varname, $value = null) {
        if ($varname != '') {
            Smarty::$global_tpl_vars[$varname] = new Smarty_variable($value);
            $ptr = $this;
            while ($ptr instanceof Smarty_Internal_Template) {
                $ptr->tpl_vars[$varname] = clone Smarty::$global_tpl_vars[$varname];
                $ptr = $ptr->parent;
            }
        }

        return $this;
    }

    /**
     * Append an element to an assigned array
     *
     * If you append to a string value, it is converted to an array value and
     * then appended to. You can explicitly pass name/value pairs, or
     * associative arrays containing the name/value pairs. If you pass the
     * optional third parameter of true, the value will be merged with the
     * current array instead of appended.
     *
     * The $merge parameter does not use the PHP array_merge function. Merging
     * two numerically indexed arrays may cause values to overwrite each other
     * or result in non-sequential keys.
     *
     * @deprecated append() and all of its derivative functions are deprecated
     *             because they do not observe scoping rules, and their current
     *             semantics prevent them from being changed in a backwards-
     *             compatible way.
     * @param  array|string         $tpl_var the template variable name(s)
     * @param  mixed                $value   the value to append
     * @param  boolean              $merge   flag if array elements shall be merged
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
     */
    public function append($tpl_var, $value = null, $merge = false) {
        if (is_array($tpl_var)) {
            // $tpl_var is an array, ignore $value
            foreach ($tpl_var as $_key => $_val) {
                $this->append($_key, $_val, $merge);
            }
            return $this;
        }

        if ($tpl_var != '' && isset($value)) {
            if (!isset($this->tpl_vars[$tpl_var])) {
                $tpl_var_inst = $this->getVariable($tpl_var, null, true, false);
                if ($tpl_var_inst instanceof Undefined_Smarty_Variable) {
                    $this->tpl_vars[$tpl_var] = new Smarty_variable(null);
                } else {
                    $this->tpl_vars[$tpl_var] = clone $tpl_var_inst;
                }
            }
            if (!(is_array($this->tpl_vars[$tpl_var]->value) || $this->tpl_vars[$tpl_var]->value instanceof ArrayAccess)) {
                settype($this->tpl_vars[$tpl_var]->value, 'array');
            }
            if ($merge && is_array($value)) {
                foreach ($value as $_mkey => $_mval) {
                    $this->tpl_vars[$tpl_var]->value[$_mkey] = $_mval;
                }
            } else {
                $this->tpl_vars[$tpl_var]->value[] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns a single or all assigned template variables
     *
     * @param  string $varname Name of variable to process, or null to return all
     * @param  Smarty_Internal_Data $_ptr Optional reference to data object
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
            if ($search_parents && isset(Smarty::$global_tpl_vars)) {
                foreach (Smarty::$global_tpl_vars AS $key => $var) {
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
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
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
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
     */
    public function clearAllAssign() {
        $this->tpl_vars = array();

        return $this;
    }

    /**
     * Load config file data and assign it to the template.
     *
     * This works identically to the {config_load} function
     *
     * @param  string $config_file Path to the config file
     * @param  string|string[]|null $sections Section name or array of section names
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
     */
    public function configLoad($config_file, $sections = null) {
        // load Config class
        $config = new Smarty_Internal_Config($config_file, $this->smarty, $this);
        $config->loadConfigVars($sections);

        return $this;
    }

    /**
     * Return the contents of an assigned variable.
     *
     * @param  string  $variable       the name of the Smarty variable
     * @param  Smarty_Internal_Data|null $_ptr Optional reference to the data object
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
        if (isset(Smarty::$global_tpl_vars[$variable])) {
            // found it, return it
            return Smarty::$global_tpl_vars[$variable];
        }
        if ($this->smarty->error_unassigned && $error_enable) {
            trigger_error('Undefined variable "' . $variable . '"', E_USER_NOTICE);
        }

        return new Undefined_Smarty_Variable;
    }

    /**
     * gets  a config variable
     *
     * @param  string $variable the name of the config variable
     * @return mixed  the value of the config variable
     */
    public function getConfigVariable($variable, $error_enable = true) {
        $_ptr = $this;
        while ($_ptr !== null) {
            if (isset($_ptr->config_vars[$variable])) {
                // found it, return it
                return $_ptr->config_vars[$variable];
            }
            // not found, try at parent
            $_ptr = $_ptr->parent;
        }
        if ($this->smarty->error_unassigned && $error_enable) {
            trigger_error('Undefined variable "' . $variable . '"', E_USER_NOTICE);
        }

        return null;
    }

    /**
     * Returns a single or all config variables
     *
     * @param  string|null $varname Variable name or null (to retrieve all)
     * @param  boolean $search_parents Whether to search parent scopes
     * @return string variable value or or array of variables
     */
    public function getConfigVars($varname = null, $search_parents = true) {
        $_ptr = $this;
        $var_array = array();
        while ($_ptr !== null) {
            if (isset($varname)) {
                if (isset($_ptr->config_vars[$varname])) {
                    return $_ptr->config_vars[$varname];
                }
            } else {
                $var_array = array_merge($_ptr->config_vars, $var_array);
            }
             // not found, try at parent
            if ($search_parents) {
                $_ptr = $_ptr->parent;
            } else {
                $_ptr = null;
            }
        }
        if (isset($varname)) {
            return '';
        } else {
            return $var_array;
        }
    }

    /**
     * Clears all loaded config variables.
     *
     * @param string|null $varname variable name or null
     * @return Smarty_Internal_Data current Smarty_Internal_Data (or Smarty or Smarty_Internal_Template) instance for chaining
     */
    public function clearConfig($varname = null) {
        if (isset($varname)) {
            unset($this->config_vars[$varname]);
        } else {
            $this->config_vars = array();
        }

        return $this;
    }

}


/**
 * class for the Smarty variable object
 *
 * This class defines the Smarty variable object
 *
 * @package Brainy
 * @subpackage Template
 */
class Smarty_Variable {
    /**
     * template variable
     *
     * @var mixed
     */
    public $value = null;
    /**
     * the scope the variable will have  (local,parent or root)
     *
     * @var int
     */
    public $scope = Smarty::SCOPE_LOCAL;

    /**
     * create Smarty variable object
     *
     * @param mixed   $value   the value to assign
     * @param int     $scope   the scope the variable will have  (local,parent or root)
     */
    public function __construct($value = null, $scope = Smarty::SCOPE_LOCAL) {
        $this->value = $value;
        $this->scope = $scope;
    }

    /**
     * <<magic>> String conversion
     *
     * @return string
     */
    public function __toString() {
        return (string) $this->value;
    }

}

/**
 * class for undefined variable object
 *
 * This class defines an object for undefined variable handling
 *
 * @package Brainy
 * @subpackage Template
 */
class Undefined_Smarty_Variable
{
    /**
     * Returns NULL
     *
     * @param  string $name
     * @return null
     */
    public function __get($name) {
        return null;
    }

    /**
     * Always returns an empty string.
     *
     * @return string
     */
    public function __toString() {
        return "";
    }

}
