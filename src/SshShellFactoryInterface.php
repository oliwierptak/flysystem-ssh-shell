<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell;

use Phuxtil\Flysystem\SshShell\Adapter\SshShellAdapter;

interface SshShellFactoryInterface
{
    public function createAdapter(SshShellConfigurator $configurator): SshShellAdapter;
}
