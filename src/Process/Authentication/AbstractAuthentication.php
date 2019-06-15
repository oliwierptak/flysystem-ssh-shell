<?php

namespace League\Flysystem\SshShell\Process\Authentication;

use League\Flysystem\SshShell\SshShellConfigurator;

abstract class AbstractAuthentication
{
    const TYPE = '';

    /**
     * @var \League\Flysystem\SshShell\SshShellConfigurator
     */
    protected $configurator;

    abstract protected function prepareAuth(): string;

    public function __construct(SshShellConfigurator $configurator)
    {
        $this->configurator = $configurator;
    }

    public function generate(): string
    {
        return $this->prepareAuth();
    }
}
