<?php

namespace Box\Brainy\Runtime;

use \Box\Brainy\Brainy;


/**
 * In-memory cache of template objects
 */
class TemplateCache
{
    private static $cache = array();
    private static $lock = 0;

    /**
     * Gets a template form the cache
     * @param  string $path
     * @param  Brainy $brainy
     * @param  string $compileID
     * @return Template|null
     */
    public static function get($path, $brainy, $compileID)
    {
        $id = self::getIDFromPath($brainy, $path, $compileID);
        return isset(self::$cache[$id]) ? self::$cache[$id] : null;
    }

    /**
     * Sets a template to the cache
     * @param Template $tpl
     * @return void
     */
    public static function set($tpl)
    {
        $id = self::getIDFromTemplate($tpl);
        self::$cache[$id] = $tpl;
    }

    /**
     * Gets the ID of the template
     * @param  Brainy $brainy
     * @param  string $path      Path to the template
     * @param  string $compileID
     * @return string
     */
    private static function getIDFromPath($brainy, $path, $compileID)
    {
        $id = $brainy->joined_template_dir . '#' . $path . $compileID;
        if (isset($id[150])) {
            $id = sha1($id);
        }
        return $id;
    }

    /**
     * Gets the ID of the template from the template object
     * @param  Template $tpl
     * @return string
     */
    private static function getIDFromTemplate($tpl)
    {
        $id = $tpl->source->unique_resource . $tpl->compile_id;
        if (isset($id[150])) {
            $id = sha1($id);
        }
        return $id;
    }

    /**
     * Locks the template cache
     * @return void
     */
    public static function lock()
    {
        self::$lock++;
    }

    /**
     * Unlocks the template cache
     * @return void
     */
    public static function unlock()
    {
        if (!self::$lock) {
            throw new Exception('Cannot unlock more times than locked');
        }
        self::$lock--;
    }

    /**
     * Clears all compiled templates
     * @return void
     */
    public static function clear()
    {
        self::$cache = array();
    }
    /**
     * Clears all compiled templates
     * @param Template $tpl The template to remove
     * @return void
     */
    public static function clearTemplate($tpl)
    {
        $id = self::getIDFromTemplate($tpl);
        if (!isset(self::$cache[$id])) {
            return;
        }
        unset(self::$cache[$id]);
    }

}
