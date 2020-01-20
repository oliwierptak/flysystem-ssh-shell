<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell;

use Phuxtil\Flysystem\SshShell\Adapter\AdapterWriter;
use Phuxtil\Flysystem\SshShell\Adapter\SshShellAdapter;
use Phuxtil\Flysystem\SshShell\Adapter\AdapterReader;
use Phuxtil\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use Phuxtil\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo;
use Phuxtil\Flysystem\SshShell\Process\Authentication\Authenticator;
use Phuxtil\Flysystem\SshShell\Process\ProcessReader;
use Phuxtil\Flysystem\SshShell\Process\ProcessWriter;
use Phuxtil\Flysystem\SshShell\Process\Scp;
use Phuxtil\Flysystem\SshShell\Process\Ssh;
use Phuxtil\Chmod\ChmodFacade;
use Phuxtil\Chmod\ChmodFacadeInterface;
use Phuxtil\Find\FindFacade;
use Phuxtil\Find\FindFacadeInterface;
use Phuxtil\Stat\StatFacade;
use Phuxtil\Stat\StatFacadeInterface;

class SshShellFactory implements SshShellFactoryInterface
{
    public function createAdapter(SshShellConfigurator $configurator): SshShellAdapter
    {
        $adapter = new SshShellAdapter(
            $this->createAdapterReader($configurator),
            $this->createAdapterWriter($configurator),
            $this->createVisibility()
        );

        $adapter->setPathPrefix($configurator->requireRoot());

        return $adapter;
    }

    protected function createAdapterReader(SshShellConfigurator $configurator): AdapterReader
    {
        return new AdapterReader(
            $this->createProcessReader($configurator),
            $this->createStatFacade(),
            $this->createFindFacade(),
            $this->createStatToSplFileInfoConverter()
        );
    }

    protected function createAdapterWriter(SshShellConfigurator $configurator): AdapterWriter
    {
        return new AdapterWriter(
            $this->createProcessWriter($configurator),
            $this->createVisibility()
        );
    }

    protected function createProcessReader(SshShellConfigurator $configurator): ProcessReader
    {
        return new ProcessReader(
            $this->createSshProcess($configurator)
        );
    }

    protected function createSshProcess(SshShellConfigurator $configurator): Ssh
    {
        return new Ssh(
            $this->createAuthenticator(),
            $configurator
        );
    }

    protected function createProcessWriter(SshShellConfigurator $configurator): ProcessWriter
    {
        return new ProcessWriter(
            $this->createScpProcess($configurator),
            $this->createSshProcess($configurator)
        );
    }

    protected function createScpProcess(SshShellConfigurator $configurator): Scp
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
