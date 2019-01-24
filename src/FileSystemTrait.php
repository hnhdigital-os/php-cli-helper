<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Yaml\Yaml;

trait FileSystemTrait
{
    /**
     * Create directory.
     *
     * @param string $path
     * @param array  $options
     *
     * @return void
     */
    protected function createDirectory($path, $options = [])
    {
        if (file_exists($path)) {
            return;
        }

        $this->exec((array_get($options, 'sudo', false) ? 'sudo ' : '').'mkdir -p "%s"', $path, $options);
    }

    /**
     * Remove directory.
     *
     * @param string $path
     *
     * @return void
     */
    protected function removeDirectory($path)
    {
        $files = glob($path.'/*');

        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }

        if (!file_exists($path)) {
            return;
        }

        rmdir($path);
    }

    /**
     * Remove a file.
     *
     * @param string $path
     * @param array  $options
     * 
     * @return void
     */
    protected function removeFile($path, $options = [])
    {
        if (!file_exists($path)) {
            return;
        }

        $this->exec((array_get($options, 'sudo', false) ? 'sudo ' : '').'unlink "%s"', $path, $options);
    }

    /**
     * Replace file contents.
     *
     * @return void
     */
    protected function replaceFileContents($path, $contents)
    {
        $tmp_path = '/tmp/'.hash('sha256', $path);
        file_put_contents($tmp_path, $contents);

        $output = $this->exec('sudo mv -f "%s" "%s"', $tmp_path, $path, ['no-verbose' => false]);
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
            $this->error($e->getMessage());

            exit();
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

    /**
     * Check if path is mounted.
     *
     * @param string $path
     * @param array  $options
     *
     * @return boolean
     */
    protected function isMounted($path, $options = [])
    {
        return !(boolean) $this->exec('mount | grep "%s" > /dev/null 2>&1; echo $?', $path, ['output' => 'last_line'] + $options);
    }

    /**
     * Mount a given path.
     *
     * @param string $source_path
     * @param string $dest_path
     * @param array  $options
     *
     * @return void
     */
    protected function mount($source_path, $dest_path, $options = [])
    {
        $this->createDirectory($dest_path, ['sudo' => array_get($options, 'sudo', false)]);

        if (!$this->isMounted($dest_path, $options)) {
            $mount_options = array_has($options, 'options') ? '-'.array_get($options, 'options') : '';

            $this->exec((array_get($options, 'sudo', false) ? 'sudo ' : '').'mount --bind %s "%s" "%s"', $mount_options, $source_path, $dest_path, $options);
        }
    }

    /**
     * Unmount a given path.
     *
     * @param string $path
     * @param array  $options
     *
     * @return void
     */
    protected function unmount($path, $options = [])
    {
        if ($this->isMounted($path)) {
            $this->exec((array_get($options, 'sudo', false) ? 'sudo ' : '').' umount -l "%s"', $path, $options);
        }
    }
}
