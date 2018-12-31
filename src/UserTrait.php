<?php

namespace HnhDigital\CliHelper;

use Symfony\Component\Process\Process;

trait UserTrait
{
    /**
     * Check if a user exists.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function userExists($name)
    {
        $process = new Process([
            'id "$NAME" -u >/dev/null 2>&1; echo $?',
        ]);

        $process->run(null, [
            'NAME' => $name,
        ]);

        return !(bool) $process->getOutput();
    }

    /**
     * Change user password.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    protected function changeUserPassword($username, $password = null)
    {
        if (is_null($password)) {
            $password = '';
        }

        $process = new Process([
            sprintf('set +o history && echo %s|%s | chpasswd && set -o history', $username, $password),
        ]);

        $process->run();
    }
}
