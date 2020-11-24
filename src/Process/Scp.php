<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Process;

class Scp extends Ssh
{
    protected function prepareCommand(string $command): string
    {
        $optionTimeout = $this->generateConnectionTimeoutOption();

        $command = sprintf(
            'scp %s -P %d %s %s',
            $optionTimeout,
            $this->configurator->requirePort(),
            $this->prepareAuth(),
            $command
        );

        return $command;
    }
}
