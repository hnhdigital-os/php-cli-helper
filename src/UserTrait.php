<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

trait UserTrait
{
    /**
     * Check if a user exists.
     *
     * @return bool
     */
    public function userExists($name)
    {
        $process = new Process([
            'id',
            $name,
            '-u',
            '>/dev/null 2>&1; echo $?'
        ]);

        $process->run();

        return !(boolean) $process->getOutput();
    }

    /**
     * Change user password.
     *
     * @return void
     */
    public function changeUserPassword($username, $password = null)
    {
        if (is_null($password)) {
            $password = '';
        }

        $process = new Process([
            'set +o history',
            '&&',
            'echo', sprintf('%s|%s', $username, $password), 
            '|',
            'chpasswd',
            '&&',
            'set -o history',
        ]);

        $process->run();
    }
}
