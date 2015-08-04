<?php
/**
 * Smarty write file plugin
 *
 * @package Brainy
 * @subpackage PluginsInternal
 * @author Monte Ohrt
 */

/**
 * Smarty Internal Write File Class
 *
 * @package Brainy
 * @subpackage PluginsInternal
 * @internal
 */
class Smarty_Internal_Write_File
{
    /**
     * Writes file in a safe way to disk
     *
     * @param  string  $_filepath complete filepath
     * @param  string  $_contents file content
     * @param  Smarty  $smarty    smarty instance
     * @return boolean true
     */
    public static function writeFile($_filepath, $_contents, Smarty $smarty) {
        if ($smarty->_file_perms !== null) {
            $old_umask = umask(0);
        }

        $_dirpath = dirname($_filepath);
        // if subdirs, create dir structure
        if ($_dirpath !== '.' && !file_exists($_dirpath)) {
            mkdir($_dirpath, $smarty->_dir_perms === null ? 0777 : $smarty->_dir_perms, true);
        }

        // write to tmp file, then move to overt file lock race condition
        $_tmp_file = $_dirpath . DIRECTORY_SEPARATOR . str_replace(array('.',','), '_', uniqid('wrt', true));
        if (!file_put_contents($_tmp_file, $_contents)) {
            throw new SmartyException("unable to write file {$_tmp_file}");
        }

        /*
         * Windows' rename() fails if the destination exists,
         * Linux' rename() properly handles the overwrite.
         * Simply unlink()ing a file might cause other processes
         * currently reading that file to fail, but linux' rename()
         * seems to be smart enough to handle that for us.
         */
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // remove original file
                unlink($_filepath);
                // rename tmp file
                $success = rename($_tmp_file, $_filepath);
            } else {
                // rename tmp file
                $success = rename($_tmp_file, $_filepath);
                if (!$success) {
                    // remove original file
                    unlink($_filepath);
                    // rename tmp file
                    $success = rename($_tmp_file, $_filepath);
                }
            }
        } catch(Exception $e) {
            $success = false;
        }

        if (!$success) {
            throw new SmartyException("unable to write file {$_filepath}");
        }

        if ($smarty->_file_perms !== null) {
            // set file permissions
            chmod($_filepath, $smarty->_file_perms);
            umask($old_umask);
        }

        return true;
    }

}
