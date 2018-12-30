<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;

trait FileSystemTrait
{
    /**
     * Remove directory.
     *
     * @param string $path
     *
     * @return void
     */
    protected function removeDirectory($path)
    {
        $files = glob($path . '/*');

        foreach ($files as $file) {
            is_dir($file) ? removeDirectory($file) : unlink($file);
        }

        rmdir($path);
    }

    /**
     * Load yaml file.
     *
     * @param string $path
     *
     * @return array
     */
    protected function loadYamlFile($path)
    {
        if (!file_exists($path)) {
            return [];
        }

        try {
            $result = Yaml::parse(file_get_contents($path));

            return is_array($result) ? $result : [];
        } catch (ParseException $e) {
            file_put_contents($user_config, '');

            return [];
        }
    }

    /**
     * Save user config.
     *
     * @param string $path
     * @param string $data
     *
     * @return array
     */
    protected function saveYamlFile($path, $data)
    {
        file_put_contents($path, Yaml::dump($data));
    }
}
