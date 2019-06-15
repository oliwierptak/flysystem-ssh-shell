<?php

namespace League\Flysystem\SshShell;

use League\Flysystem\SshShell\Adapter\AdapterWriter;
use League\Flysystem\SshShell\Adapter\SshShellAdapter;
use League\Flysystem\SshShell\Adapter\AdapterReader;
use League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use League\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo;
use League\Flysystem\SshShell\Process\Authentication\Authenticator;
use League\Flysystem\SshShell\Process\ProcessReader;
use League\Flysystem\SshShell\Process\ProcessWriter;
use League\Flysystem\SshShell\Process\Scp;
use League\Flysystem\SshShell\Process\Ssh;
use Phuxtil\Chmod\ChmodFacade;
use Phuxtil\Chmod\ChmodFacadeInterface;
use Phuxtil\Find\FindFacade;
use Phuxtil\Find\FindFacadeInterface;
use Phuxtil\Stat\StatFacade;
use Phuxtil\Stat\StatFacadeInterface;

class SshShellFactory implements SshShellFactoryInterface
{
    public function createAdapter(Configurator $configurator): SshShellAdapter
    {
        $adapter = new SshShellAdapter(
            $this->createAdapterReader($configurator),
            $this->createAdapterWriter($configurator),
            $this->createVisibility()
        );

        $adapter->setPathPrefix($configurator->getRoot());

        return $adapter;
    }

    protected function createAdapterReader(Configurator $configurator): AdapterReader
    {
        return new AdapterReader(
            $this->createProcessReader($configurator),
            $this->createStatFacade(),
            $this->createFindFacade(),
            $this->createStatToSplFileInfoConverter()
        );
    }

    protected function createAdapterWriter(Configurator $configurator): AdapterWriter
    {
        return new AdapterWriter(
            $this->createProcessWriter($configurator),
            $this->createVisibility()
        );
    }

    protected function createProcessReader(Configurator $configurator): ProcessReader
    {
        return new ProcessReader(
            $this->createSshProcess($configurator)
        );
    }

    protected function createSshProcess(Configurator $configurator): Ssh
    {
        return new Ssh(
            $this->createAuthenticator(),
            $configurator
        );
    }

    protected function createProcessWriter(Configurator $configurator): ProcessWriter
    {
        return new ProcessWriter(
            $this->createScpProcess($configurator),
            $this->createSshProcess($configurator)
        );
    }

    protected function createScpProcess(Configurator $configurator): Scp
    {
        return new Scp(
            $this->createAuthenticator(),
            $configurator
        );
    }

    protected function createAuthenticator(): Authenticator
    {
        return new Authenticator();
    }

    protected function createStatFacade(): StatFacadeInterface
    {
        return new StatFacade();
    }

    protected function createChmodFacade(): ChmodFacadeInterface
    {
        return new ChmodFacade();
    }

    protected function createFindFacade(): FindFacadeInterface
    {
        return new FindFacade();
    }

    protected function createStatToSplFileInfoConverter()
    {
        return new StatToSplFileInfo(
            $this->createChmodFacade()
        );
    }

    protected function createVisibility(): VisibilityPermissionConverter
    {
        return new VisibilityPermissionConverter();
    }
}
