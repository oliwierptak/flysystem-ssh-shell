<?php

namespace League\Flysystem\SshShell\Process\Authentication;

use League\Flysystem\SshShell\Configurator;

abstract class AbstractAuthentication
{
    const TYPE = '';

    /**
     * @var \League\Flysystem\SshShell\Configurator
     */
    protected $configurator;

    abstract protected function prepareAuth(): string;

    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    public function generate(): string
    {
        return $this->prepareAuth();
    }
}
