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

        $this->exec('mkdir -p "%s"', $path, $options);
    }

    /**
     * Remove directory.
     *
     * @param string $path
     * @param array  $options
     *
     * @return void
     */
    protected function removeDirectory($path, $options = [])
    {
        if (!file_exists($path)) {
            return;
        }

        if (!empty($this->cwd) && !array_has($options, 'chroot') && stripos($path, $this->cwd) === false) {
            $this->error('Attempted to remove path outside of working directory.');

            return;
        }

        $this->exec('rm -rf "%s"', $path, $options);
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

        if (!empty($this->cwd) && !array_has($options, 'chroot') && stripos($path, $this->cwd) === false) {
            $this->error('Attempted to remove path outside of working directory.');

            return;
        }

        $this->exec('unlink "%s"', $path, $options);
    }

    /**
     * Copy a file from one location to another.
     *
     * @param string $source_path
     * @param string $dest_path
     * @param array  $options
     *
     * @return void
     */
    protected function copyFile($source_path, $dest_path, $options = [])
    {
        if (!empty($this->cwd) && !array_has($options, 'chroot') && stripos($dest_path, $this->cwd) === false) {
            $this->error('Attempted to copy to a path outside of working directory.');

            return;
        }

        $this->exec('cp -R "%s" "%s"', $source_path, $dest_path, $options);
    }

    /**
     * Replace file contents.
     *
     * @return void
     */
    protected function putFileContents($path, $contents, $options = [])
    {
        $tmp_path = '/tmp/'.hash('sha256', $path);
        file_put_contents($tmp_path, $contents);

        $output = $this->exec('mv -f "%s" "%s"', $tmp_path, $path, [
            'no-verbose' => false
        ] + $options);
    }

    /**
     * Chmod a path.
     *
     * @param string $path
     * @param array  $options
     *
     * @return void
     */
    protected function chmod($path, $mod, $options = [])
    {
        $this->exec('chmod %s "%s"', $mod, $path, $options);
    }

    /**
     * Chmod all files in path.
     *
     * @param string $path
     * @param array  $options
     *
     * @return void
     */
    protected function chmodAll($path, $mod, $options = [])
    {
        $this->exec('chmod -R %s "%s"', $mod, $path, $options);
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
        return !(boolean) $this->exec('mount | grep "%s" > /dev/null 2>&1; echo $?', $path, [
            'return' => 'last_line'
        ] + $options);
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
            $mount_type = array_has($options, 'type') ? array_get($options, 'type') : '--bind';

            $command = sprintf(
                'mount %s %s "%s" "%s"',
                $mount_type,
                $mount_options,
                $source_path,
                $dest_path
            );

            // Fix when source_path is set to none.
            $command = str_replace('"none"', 'none', $command);
            $this->exec($command, $options);
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
        if ($this->isMounted($path, $options)) {
            $this->exec('umount -l "%s"', $path, $options);
        }
    }
}
