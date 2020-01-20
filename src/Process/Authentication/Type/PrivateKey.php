<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Process\Authentication\Type;

use Phuxtil\Flysystem\SshShell\Process\Authentication\AbstractAuthentication;

class PrivateKey extends AbstractAuthentication
{
    const TYPE = 'privateKey';

    protected function prepareAuth(): string
    {
        return sprintf(
            '-i %s',
            $this->configurator->getPrivateKey()
        );
    }
}
