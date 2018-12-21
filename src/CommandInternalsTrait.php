<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Yaml\Yaml;

trait CommandInternalsTrait
{
    /**
     * Get default working directory.
     *
     * @return string
     */
    private function getDefaultWorkingDirectory($file_name = '', $is_file = false)
    {
        $path = env('XDG_RUNTIME_DIR') ? env('XDG_RUNTIME_DIR') : $this->getUserHome();
        $path = empty($path) ? $_SERVER['TMPDIR'] : $path;
        $path .= '/.'.config('app.directory');

        if (!empty($file_name)) {
            $path .= '/'.$file_name;
        }

        return $this->checkWorkingDirectory($path, $is_file);
    }

    /**
     * Return the user's home directory.
     */
    private function getUserHome()
    {
        // Linux home directory
        $home = getenv('HOME');

        if (!empty($home)) {
            $home = rtrim($home, '/');
        }

        // Windows home directory
        elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            $home = rtrim($_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'], '\\/');
        }

        return empty($home) ? null : $home;
    }

    /**
     * Check working directory.
     *
     * @return string
     */
    private function checkWorkingDirectory($path, $check_file = true)
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
}
