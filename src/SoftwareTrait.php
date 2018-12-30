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
    public function packageInstall($name)
    {        
        $process = new Process([
            'sudo dpkg -s "$NAME" | grep Status >/dev/null 2>&1; echo $?',
        ]);

        $process->run(null, [
            'NAME' => $name
        ]);

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
            'command -v "$NAME" >/dev/null 2>&1; echo $?',
        ]);

        $process->run(null, [
            'NAME' => $name
        ]);

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
            'service --status-all | grep -Fq "$NAME" >/dev/null 2>&1; echo $?',
        ]);

        $process->run(null, [
            'NAME' => $name
        ]);

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
        if (is_array(array_get($packages, 0))) {
            $packages = array_get($packages, 0);
        }

        list($quiet_arg_1, $quiet_arg_2) = $this->getVerboseArguments();

        foreach ($packages as $package) {
            if ($this->packageInstalled($package)) {
                $this->line(sprintf('Installed %s ✔️', $package));
                continue;
            }

            $this->line(sprintf('Installing %s ✔️', $package));

            $process = new Process([
                'sudo apt-get $Q_ARG_1 install "$PACKAGE" -y $Q_ARG_2',
            ]);

            $process->setTimeout(null)
                ->run(null, [
                    'PACKAGE' => $package,
                    'Q_ARG_1' => $quiet_arg_1,
                    'Q_ARG_2' => $quiet_arg_2,
                ]);
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
            'sudo apt-add-repository "$NAME" -y $Q_ARG_2',
        ]);

        $process->setTimeout(null)
            ->run(null, [
                'NAME' => $name,
                'Q_ARG_2' => $quiet_arg_2,
            ]);

        // Update repo.
        $process = new Process([
            'sudo apt-get $Q_ARG_1 update $Q_ARG_2'
        ]);

        $process->setTimeout(null)
            ->run(null, [
                'NAME' => $name,
                'Q_ARG_1' => $quiet_arg_1,
                'Q_ARG_2' => $quiet_arg_2,
            ]);
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
                'sudo apt-get  $Q_ARG_1 remove "$PACKAGE" -y  $Q_ARG_2'
            ]);

            $process->setTimeout(null)
                ->run(null, [
                    'PACKAGE' => $package,
                    'Q_ARG_1' => $quiet_arg_1,
                    'Q_ARG_2' => $quiet_arg_2,
                ]);
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
                'sudo pip install --upgrade "$PACKAGE"'
            ]);

            $process->setTimeout(null)
                ->run(null, [
                    'PACKAGE' => $package
                ]);
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
