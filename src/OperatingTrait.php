<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

trait OperatingTrait
{
    /**
     * Check if running in root.
     *
     * @return void
     */
    protected function isRoot()
    {
        return $this->exec('whoami') === 'root';        
    }

    /**
     * Is verbose.
     *
     * @return boolean
     */
    protected function isVerbose()
    {
        return $this->hasVerbose();
    }

    /**
     * Is verbose.
     *
     * @return boolean
     */
    protected function hasVerbose($level = '')
    {
        switch ($level) {
            case 'v':
                return $this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
            case 'vv':
                return $this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
            case 'vvv':
                return $this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
            case 'q':
                return $this->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_QUIET;
        }

        return $this->getOutput()->getVerbosity() > OutputInterface::VERBOSITY_NORMAL;
    }

    /**
     * Find and replace.
     *
     * @param string $find
     * @param string $replace
     * @param string $path
     *
     * @return void
     */
    public function sed($find, $replace, $path, $options = [])
    {
        if (file_exists($path) && is_file($path)) {
            //$.*/[\]^"
            $escape_list = ['\\', '/', '$', '.', '*', '[', ']', '^', '"'];
            $replacement_list = ['\\\\', '\/', '\$', '\.', '\*','\[', '\]', '\^', '\"'];

            $find = str_replace($escape_list, $replacement_list, $find);
            $replace = str_replace($escape_list, $replacement_list, $replace);

            $this->exec('sed -i "s/%s/%s/g" "%s"', $find, $replace, $path, $options);
        }
    }

    /**
     * Execute a command.
     *
     * @param string $command
     * @param array  ...$variables
     *
     * @return mixed
     */
    protected function exec($command, ...$variables)
    {
        $options = [];

        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $options = $value;
                unset($variables[$key]);
                break;
            }
        }

        if (count($variables)) {
            $command = sprintf($command, ...$variables);
        }

        if (array_has($options, 'chroot')) {
            $command = sprintf('sudo chroot "%s" /bin/bash -c "su - -c \'%s\'" 2>&1', array_get($options, 'chroot'), addcslashes($command, '"'));
        }

        if ($this->hasVerbose('v')) {
            $this->line(sprintf('[<info>EXEC</info>] <comment>%s</comment>', $command));
            $this->line(sprintf('[<info>EXEC</info>] Timeout: <comment>%s</comment>', array_get($options, 'timeout', 3600) ?? 'None'));
            $this->line(sprintf('[<info>EXEC</info>] Idle Timeout: <comment>%s</comment>', array_get($options, 'idle-timeout', 60) ?? 'None'));
            $this->line('');
        }

        $process = new Process($command);
        $process->setTimeout(array_get($options, 'timeout', 3600));
        $process->setIdleTimeout(array_get($options, 'idle-timeout', 60));

        $process->start();

        $output_text = '';
        
        try {
            foreach ($process as $type => $data) {
                if ($type == 'out') {
                    $output_text .= $data;
                }

                if (!$this->hasVerbose('vv')) {
                    continue;
                }

                if (empty(trim($data))) {
                    continue;
                }

                switch ($type) {
                    case 'out':
                        $this->line('<fg=blue>'.trim($data).'</>');
                        break;
                    case 'err':
                        $this->line('<fg=cyan>'.trim($data).'</>');
                        break;
                }
            }
            
            $exit_code = $process->getExitCode();

            if ($this->hasVerbose('v')) {
                $this->line('');
                $this->line(sprintf('[<info>EXEC</info>] exit code: <comment>%s</comment>', $exit_code));
            }

            if (array_get($options, 'return') === 'exit_code') {
                return $exit_code;
            }

            $output = [];

            foreach (explode("\n", trim($output_text)) as $key => $line) {
                if (stripos($line, 'mesg: ') !== false || empty($line)) {
                    continue;
                }

                $output[] = $line;
            }

            if (array_get($options, 'return') === 'all') {
                return [$exit_code, $output];
            }

            if (array_get($options, 'return') === 'output') {
                return $output;
            }

            if (array_get($options, 'return') === 'last_line') {
                return array_pop($output);
            }

            if (array_get($options, 'return') === 'output_string') {
                return implode("\n", $output);
            }

            return $output;
        } catch (\Exception $exception) {
            $this->line(sprintf('[<info>EXEC</info>] <error>%s</error>', $exception->getMessage()));
        }

        return false;
    }
}
