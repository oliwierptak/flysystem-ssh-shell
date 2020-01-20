<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Process\Authentication;

use Phuxtil\Flysystem\SshShell\SshShellConfigurator;

abstract class AbstractAuthentication
{
    const TYPE = '';

    /**
     * @var \Phuxtil\Flysystem\SshShell\SshShellConfigurator
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
