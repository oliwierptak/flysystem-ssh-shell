<?php

namespace League\Flysystem\SshShell\Process\Authentication\Type;

use League\Flysystem\SshShell\Process\Authentication\AbstractAuthentication;

class Config extends AbstractAuthentication
{
    const TYPE = 'config';

    protected function prepareAuth(): string
    {
        return '';
    }
}
