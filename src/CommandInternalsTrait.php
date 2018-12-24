<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Yaml\Yaml;

trait CommandInternalsTrait
{
    /**
     * Get config path.
     *
     * @return string
     */
    private function getConfigPath($file_name = '', $is_file = false)
    {
        $path = $this->getUserHome();
        $path = empty($path) ? $_SERVER['TMPDIR'] : $path;
        $path .= '/.'.config('app.directory');

        if (!empty($file_name)) {
            $path .= '/'.$file_name;
        }

        return $this->checkPath($path, $is_file);
    }

    /**
     * Get temporary path.
     *
     * @return string
     */
    private function getTempPath($file_name = '', $is_file = false)
    {
        $path = env('XDG_RUNTIME_DIR') ? env('XDG_RUNTIME_DIR') : $this->getUserHome('tmp');
        $path = empty($path) ? $_SERVER['TMPDIR'] : $path;
        $path .= '/.'.config('app.directory');

        if (!empty($file_name)) {
            $path .= '/'.$file_name;
        }

        return $this->checkPath($path, $is_file);
    }

    /**
     * Check path.
     *
     * @return string
     */
    private function checkPath($path, $check_file = true)
    {
        // Create working directory.
        if (!file_exists($dirname_path = $check_file ? dirname($path) : $path)) {
            mkdir($dirname_path, 0755, true);
        }

        // Create empty file.
        if ($check_file && !file_exists($path)) {
            file_put_contents($path, '');
        }

        return $path;
    }

    /**
     * Return the user's home directory.
     */
    private function getUserHome($path = '')
    {
        // Linux home directory
        $home_path = getenv('HOME');

        if (!empty($home_path)) {
            $home_path = rtrim($home_path, '/');
        }

        // Windows home directory
        elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            $home_path = rtrim($_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'], '\\/');
        }

        $home_path = empty($home_path) ? null : $home_path;

        if (!empty($home_path) && !empty($path)) {
            return $home_path.'/'.$path;
        }

        return $home_path;
    }
}
