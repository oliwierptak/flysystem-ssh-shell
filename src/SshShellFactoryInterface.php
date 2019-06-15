<?php

namespace League\Flysystem\SshShell;

use League\Flysystem\SshShell\Adapter\SshShellAdapter;

interface SshShellFactoryInterface
{
    public function createAdapter(SshShellConfigurator $configurator): SshShellAdapter;
}
