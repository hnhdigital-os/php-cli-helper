<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

trait SoftwareTrait
{
    /**
     * Check if a package is installed.
     *
     * @return bool
     */
    public function packageInstalled($name)
    {        
        $process = new Process([
            'sudo',
            'dpkg',
            '-s',
            $name,
            '|',
            'grep',
            'Status',
            '>/dev/null 2>&1; echo $?',
        ]);

        $process->run();

        return !(boolean) $process->getOutput();
    }

    /**
     * Check if a binary exists.
     *
     * @return bool
     */
    public function binaryExists($name)
    {
        $process = new Process([
            'command',
            '-v',
            $name,
            '>/dev/null 2>&1; echo $?',
        ]);

        $process->run();

        return !(boolean) $process->getOutput();
    }

    /**
     * Check if a service exists.
     *
     * @return bool
     */
    public function serviceExists($name)
    {
        $process = new Process([
            'service',
            '--status-all',
            '|',
            'grep',
            '-Fq',
            $name,
            '>/dev/null 2>&1; echo $?',
        ]);

        $process->run();

        return !(boolean) $process->getOutput();
    }

    /**
     * Install packages.
     *
     * @param arguments $packages
     *
     * @return void
     */
    public function aptInstall(...$packages)
    {
        list($quiet_arg_1, $quiet_arg_2) = $this->getVerboseArguments();

        foreach ($packages as $package) {
            if ($this->packageInstalled($package)) {
                continue;
            }

            $this->info(sprintf('Installing %s...', $package));

            $process = new Process([
                'sudo',
                'apt-get',
                $quiet_arg_1,
                'install',
                $package,
                '-y'
                $quiet_arg_2,
            ]);

            $process->setTimeout(null)
                ->run();
        }
    }

    /**
     * Add a repo.
     *
     * @param string $name
     *
     * @return void
     */
    public function aptAddRepo($name)
    {
        list($quiet_arg_1, $quiet_arg_2) = $this->getVerboseArguments();

        // Add the repository.
        $process = new Process([
            'sudo',
            'apt-add-repository',
            $name,
            '-y',
            $quiet_arg_2,
        ]);

        $process->setTimeout(null)
            ->run();

        // Update repo.
        $process = new Process([
            'sudo',
            'apt-get',
            $quiet_arg_1,
            'update',
            $quiet_arg_2,
        ]);

        $process->setTimeout(null)
            ->run();
    }

    /**
     * Remove packages.
     *
     * @param arguments $packages
     *
     * @return void
     */
    public function aptRemove(...$packages)
    {
        list($quiet_arg_1, $quiet_arg_2) = $this->getVerboseArguments();

        foreach ($packages as $package) {
            if (!$this->packageInstalled($package)) {
                continue;
            }

            $process = new Process([
                'sudo',
                'apt-get',
                $quiet_arg_1,
                'remove,'
                $package,
                '-y',
                $quiet_arg_2,
            ]);

            $process->setTimeout(null)
                ->run();
        }
    }

    /**
     * Install python package.
     *
     * @param  string $packages
     *
     * @return void
     */
    public function pipInstall(...$packages)
    {
        if (!$this->packageInstalled('python-pip')) {
            return false;
        }

        foreach ($packages as $package) {
            $process = new Process([
                'sudo',
                'pip',
                'install'
                '--upgrade',
                $package
            ]);

            $process->setTimeout(null)
                ->run();
        }
    }

    /**
     * Get the verbose arguments.
     *
     * @return array
     */
    public function getVerboseArguments()
    {
        $quiet_arg_1 = $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE ? '-qq' : '';
        $quiet_arg_2 = $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE ? '2>&1 /dev/null' : '';

        return [
            $quiet_arg_1,
            $quiet_arg_2,
        ];
    }
}
