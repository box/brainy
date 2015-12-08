<?php

namespace Box\Brainy\Runtime;

class PluginLoader
{
    /**
     * Loads a plugin
     * @param  string             $type   The type of the plugin
     * @param  string             $name   The plugin name
     * @param  \Box\Brainy\Brainy $brainy The instance of Brainy to load it for
     * @return bool Whether the plugin was loaded successfully
     */
    public static function loadPlugin($type, $name, $brainy)
    {
        $function = self::getPluginFunction($type, $name);
        if (is_callable($function)) {
            return true;
        }

        $filename = self::getPluginFilename($type, $name);

        $pluginDirs = $brainy->getPluginsDir();
        foreach ($pluginDirs as $dir) {
            $ffile = $dir . $filename;
            if (!file_exists($ffile)) {
                continue;
            }
            include $ffile;
            return true;
        }

        return false;
    }

    /**
     * Return the filename of the plugin given its name and type
     * @param  string $type The type of the plugin
     * @param  string $name The name of the plugin
     * @return string       The filename of the plugin
     */
    public static function getPluginFilename($type, $name)
    {
        return $type . '.' . $name . '.php';
    }

    /**
     * Return the name of a plugin function given its name and type
     * @param  string $type The type of the plugin
     * @param  string $name The name of the plugin
     * @return string       The name of the plugin's function
     */
    public static function getPluginFunction($type, $name)
    {
        return 'smarty_' . $type . '_' . $name;
    }
}
