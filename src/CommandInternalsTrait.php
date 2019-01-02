<?php

namespace HnhDigital\CliHelper;

trait CommandInternalsTrait
{
    /**
     * Get config path.
     *
     * @param string $file_name
     * @param bool   $is_file
     *
     * @return string
     */
    protected function getConfigPath($file_name = '', $is_file = false)
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
     * @param string $file_name
     * @param bool   $is_file
     *
     * @return string
     */
    protected function getTempPath($file_name = '', $is_file = false)
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
     * @param string $path
     * @param bool   $check_file
     *
     * @return string
     */
    protected function checkPath($path, $check_file = true)
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
     * Get the current user home path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getUserHome($path = '')
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

    /**
     * Get binary path.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getBinaryPath()
    {
        return realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
    }
}
