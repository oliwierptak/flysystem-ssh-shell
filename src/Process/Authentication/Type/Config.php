<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Process\Authentication\Type;

use Phuxtil\Flysystem\SshShell\Process\Authentication\AbstractAuthentication;

class Config extends AbstractAuthentication
{
    const TYPE = 'config';

    protected function prepareAuth(): string
    {
        return '';
    }
}
