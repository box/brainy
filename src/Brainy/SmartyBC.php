<?php
/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        SmartyBC.class.php
 * SVN:         $Id: $
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link       http://www.smarty.net/
 * @copyright  2008 New Digital Group, Inc.
 * @author     Monte Ohrt <monte at ohrt dot com>
 * @author     Uwe Tews
 * @author     Rodney Rehm
 * @author     Matt Basta
 * @package    Brainy
 * @deprecated Use the Smarty class instead.
 */

namespace Box\Brainy;

/**
 * Smarty Backward Compatibility Wrapper Class
 *
 * @package Brainy
 */
class SmartyBC extends Brainy
{
    /**
     * Smarty 2 BC
     * @var string
     */
    public $_version = self::SMARTY_VERSION;

    /**
     * Initialize new SmartyBC object
     *
     * @param array $options options to set during initialization, e.g. array( 'forceCompile' => false )
     */
    public function __construct(array $options = array())
    {
        parent::__construct();
    }

    /**
     * clear the given assigned template variable.
     *
     * @param string $tpl_var the template variable to clear
     */
    public function clear_assign($tpl_var)
    {
        $this->clearAssign($tpl_var);
    }

    /**
     * Registers custom function to be used in templates
     *
     * @param string $function      the name of the template function
     * @param string $function_impl the name of the PHP function to register
     * @param bool   $cacheable
     * @param mixed  $cache_attrs
     */
    public function register_function($function, $function_impl, $cacheable = true, $cache_attrs = null)
    {
        $this->registerPlugin('function', $function, $function_impl, $cacheable, $cache_attrs);
    }

    /**
     * Unregisters custom function
     *
     * @param string $function name of template function
     */
    public function unregister_function($function)
    {
        $this->unregisterPlugin('function', $function);
    }

    /**
     * Registers compiler function
     *
     * @param string $function      name of template function
     * @param string $function_impl name of PHP function to register
     * @param bool   $cacheable
     */
    public function register_compiler_function($function, $function_impl, $cacheable = true)
    {
        $this->registerPlugin('compiler', $function, $function_impl, $cacheable);
    }

    /**
     * Unregisters compiler function
     *
     * @param string $function name of template function
     */
    public function unregister_compiler_function($function)
    {
        $this->unregisterPlugin('compiler', $function);
    }

    /**
     * Registers modifier to be used in templates
     *
     * @param string $modifier      name of template modifier
     * @param string $modifier_impl name of PHP function to register
     */
    public function register_modifier($modifier, $modifier_impl)
    {
        $this->registerPlugin('modifier', $modifier, $modifier_impl);
    }

    /**
     * Unregisters modifier
     *
     * @param string $modifier name of template modifier
     */
    public function unregister_modifier($modifier)
    {
        $this->unregisterPlugin('modifier', $modifier);
    }

    /**
     * Registers a resource to fetch a template
     *
     * @param string $type      name of resource
     * @param array  $functions array of functions to handle resource
     */
    public function register_resource($type, $functions)
    {
        $this->registerResource($type, $functions);
    }

    /**
     * Unregisters a resource
     *
     * @param string $type name of resource
     */
    public function unregister_resource($type)
    {
        $this->unregisterResource($type);
    }

    /**
     * clear cached content for the given template and cache id
     *
     * @param      string $tpl_file   name of template file
     * @param      string $cache_id   name of cache_id
     * @param      string $compile_id name of compile_id
     * @param      string $exp_time   expiration time
     * @return     boolean
     * @deprecated This is a no-op.
     */
    public function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null)
    {
        return false;
    }

    /**
     * clear the entire contents of cache (all templates)
     *
     * @param      string $exp_time expire time
     * @return     boolean
     * @deprecated This is a no-op.
     */
    public function clear_all_cache($exp_time = null)
    {
        return false;
    }

    /**
     * test to see if valid cache exists for this template
     *
     * @param      string $tpl_file   name of template file
     * @param      string $cache_id
     * @param      string $compile_id
     * @return     boolean
     * @deprecated This is a no-op
     * @see        Smarty_Internal_TemplateBase::isCached() Information on why this is deprecated
     */
    public function is_cached($tpl_file, $cache_id = null, $compile_id = null)
    {
        return false;
    }

    /**
     * clear all the assigned template variables.
     */
    public function clear_all_assign()
    {
        $this->clearAllAssign();
    }

    /**
     * clears compiled version of specified template resource,
     * or all compiled template files if one is not specified.
     * This function is for advanced use only, not normally needed.
     *
     * @param  string $tpl_file
     * @param  string $compile_id
     * @param  string $exp_time
     * @return boolean results of {@link smarty_core_rm_auto()}
     */
    public function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null)
    {
        return $this->clearCompiledTemplate($tpl_file, $compile_id, $exp_time);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param  string $tpl_file
     * @return boolean
     */
    public function template_exists($tpl_file)
    {
        return $this->templateExists($tpl_file);
    }

    /**
     * Returns an array containing template variables
     *
     * @param  string $name
     * @return array
     */
    public function get_template_vars($name = null)
    {
        return $this->getTemplateVars($name);
    }

    /**
     * return a reference to a registered object
     *
     * @param  string $name
     * @return object
     */
    public function get_registered_object($name)
    {
        return $this->getRegisteredObject($name);
    }

    /**
     * trigger Smarty error
     *
     * @param string  $error_msg
     * @param integer $error_type
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error("Smarty error: $error_msg", $error_type);
    }
}
