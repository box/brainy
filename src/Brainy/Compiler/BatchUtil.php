<?php
/**
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
 * @link      http://www.smarty.net/
 * @copyright 2008 New Digital Group, Inc.
 * @author    Monte Ohrt <monte at ohrt dot com>
 * @author    Uwe Tews
 * @package   Brainy
 */

namespace Box\Brainy\Compiler;


class BatchUtil
{
    /**
     * private constructor to prevent calls creation of new instances
     */
    final private function __construct() 
    {
    }

    /**
     * Compile all template files
     *
     * @param  string             $extension     template file name extension
     * @param  bool               $force_compile force all to recompile
     * @param  int                $time_limit    set maximum execution time
     * @param  int                $max_errors    set maximum allowed errors
     * @param  \Box\Brainy\Brainy $smarty        \Box\Brainy\Brainy instance
     * @return integer number of template files compiled
     */
    public static function compileAllTemplates($extension, $force_compile, $time_limit, $max_errors, \Box\Brainy\Brainy $smarty)
    {
        // switch off time limit
        if (function_exists('set_time_limit')) {
            @set_time_limit($time_limit);
        }
        $smarty->force_compile = $force_compile;
        $_count = 0;
        $_error_count = 0;
        // loop over array of template directories
        foreach ($smarty->getTemplateDir() as $_dir) {
            $_compileDirs = new \RecursiveDirectoryIterator($_dir);
            $_compile = new \RecursiveIteratorIterator($_compileDirs);
            foreach ($_compile as $_fileinfo) {
                $_file = $_fileinfo->getFilename();
                if (substr(basename($_fileinfo->getPathname()), 0, 1) == '.' || strpos($_file, '.svn') !== false) { continue; 
                }
                if (!substr_compare($_file, $extension, - strlen($extension)) == 0) { continue; 
                }
                if ($_fileinfo->getPath() == substr($_dir, 0, -1)) {
                    $_template_file = $_file;
                } else {
                    $_template_file = substr($_fileinfo->getPath(), strlen($_dir)) . DIRECTORY_SEPARATOR . $_file;
                }
                echo '<br>', $_dir, '---', $_template_file;
                flush();
                $_start_time = microtime(true);
                try {
                    $_tpl = $smarty->createTemplate($_template_file);
                    if ($_tpl->mustCompile()) {
                        $_tpl->compileTemplateSource();
                        $_count++;
                        echo ' compiled in  ', microtime(true) - $_start_time, ' seconds';
                        flush();
                    } else {
                        echo ' is up to date';
                        flush();
                    }
                } catch (\Exception $e) {
                    echo 'Error: ', $e->getMessage(), "<br><br>";
                    $_error_count++;
                }
                // free memory
                $_tpl = null;
                if ($max_errors !== null && $_error_count == $max_errors) {
                    echo '<br><br>too many errors';
                    exit();
                }
            }
        }

        return $_count;
    }

    /**
     * Delete compiled template file
     *
     * @param  string             $resource_name template name
     * @param  string             $compile_id    compile id
     * @param  integer            $exp_time      expiration time
     * @param  \Box\Brainy\Brainy $smarty        \Box\Brainy\Brainy instance
     * @return integer number of template files deleted
     */
    public static function clearCompiledTemplate($resource_name, $compile_id, $exp_time, \Box\Brainy\Brainy $smarty)
    {
        $_compile_dir = realpath($smarty->getCompileDir()).'/';
        $_compile_id = isset($compile_id) ? preg_replace('![^\w\|]+!', '_', $compile_id) : null;
        $_dir_sep = $smarty->use_sub_dirs ? '/' : '^';
        if (isset($resource_name)) {
            $tpl = new \Box\Brainy\Templates\Template($resource_name, $smarty);

            // remove from template cache
            if (!$tpl->source->exists) {
                return 0;
            }
            $_resource_part_1 = basename(str_replace('^', '/', $tpl->compiled->filepath));
            $_resource_part_1_length = strlen($_resource_part_1);
        }
        $_dir = $_compile_dir;
        if ($smarty->use_sub_dirs && isset($_compile_id)) {
            $_dir .= $_compile_id . $_dir_sep;
        }
        if (isset($_compile_id)) {
            $_compile_id_part = str_replace('\\', '/', $_compile_dir . $_compile_id . $_dir_sep);
            $_compile_id_part_length = strlen($_compile_id_part);
        }
        $_count = 0;
        try {
            $_compileDirs = new \RecursiveDirectoryIterator($_dir);
        } catch (\UnexpectedValueException $e) {
            // NOTE: UnexpectedValueException thrown for PHP >= 5.3
            return 0;
        }
        $_compile = new \RecursiveIteratorIterator($_compileDirs, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($_compile as $_file) {
            if (substr(basename($_file->getPathname()), 0, 1) == '.' || strpos($_file, '.svn') !== false) {
                continue; 
            }

            $_filepath = str_replace('\\', '/', (string) $_file);

            if ($_file->isDir()) {
                if (!$_compile->isDot()) {
                    // delete folder if empty
                    @rmdir($_file->getPathname());
                }
            } else {
                $unlink = false;
                if ((!isset($_compile_id) || (isset($_filepath[$_compile_id_part_length]) && $a = !strncmp($_filepath, $_compile_id_part, $_compile_id_part_length)))
                    && (!isset($resource_name)
                    || (isset($_filepath[$_resource_part_1_length])
                    && substr_compare($_filepath, $_resource_part_1, -$_resource_part_1_length, $_resource_part_1_length) == 0))
                ) {
                    if (isset($exp_time)) {
                        if (time() - @filemtime($_filepath) >= $exp_time) {
                            $unlink = true;
                        }
                    } else {
                        $unlink = true;
                    }
                }

                if ($unlink && @unlink($_filepath)) {
                    $_count++;
                }
            }
        }
        // clear compiled cache
        \Box\Brainy\Resources\Resource::reset();

        return $_count;
    }

}
