<?php

namespace Phuxtil\Flysystem\SshShell\Process;

class Scp extends Ssh
{
    protected function prepareCommand(string $command): string
    {
        $command = sprintf(
            'scp -P %d %s %s',
            $this->configurator->getPort(),
            $this->prepareAuth(),
            $command
        );

        return $command;
    }
}
