<?php

namespace League\Flysystem\SshShell;

use League\Flysystem\SshShell\Adapter\SshShellAdapter;

interface SshShellFactoryInterface
{
    public function createAdapter(Configurator $configurator): SshShellAdapter;
}
