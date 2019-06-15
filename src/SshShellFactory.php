<?php

namespace League\Flysystem\SshShell;

use League\Flysystem\SshShell\Adapter\AdapterWriter;
use League\Flysystem\SshShell\Adapter\Response\Decorator\ResponseDecoratorContainer;
use League\Flysystem\SshShell\Adapter\SshShellAdapter;
use League\Flysystem\SshShell\Adapter\AdapterReader;
use League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use League\Flysystem\SshShell\FileInfo\SshFileInfo;
use League\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo;
use League\Flysystem\SshShell\Process\Authentication\Authenticator;
use League\Flysystem\SshShell\Process\ProcessReader;
use League\Flysystem\SshShell\Process\ProcessWriter;
use League\Flysystem\SshShell\Process\Scp;
use League\Flysystem\SshShell\Process\Ssh;
use Phuxtil\Chmod\ChmodFacade;
use Phuxtil\Stat\StatFacade;

class SshShellFactory
{
    public function createSshFileInfo(string $path, Configurator $configurator): SshFileInfo
    {
        return new SshFileInfo(
            $path,
            $this->createAdapterReader($configurator),
            $this->createProcessReader($configurator)
        );
    }

    public function createAdapter(Configurator $configurator): SshShellAdapter
    {
        $adapter = new SshShellAdapter(
            $this->createAdapterReader($configurator),
            $this->createAdapterWriter($configurator),
            $this->createVisibility(),
            $this->createResponseDecoratorContainer()->collect()
        );

        $adapter->setPathPrefix($configurator->getRoot());

        return $adapter;
    }

    public function createAdapterReader(Configurator $configurator): AdapterReader
    {
        return new AdapterReader(
            $this->createProcessReader($configurator),
            $this->createStatFacade(),
            $this->createStatToSplFileInfoConverter()
        );
    }

    public function createAdapterWriter(Configurator $configurator): AdapterWriter
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

    protected function createStatFacade(): StatFacade
    {
        return new StatFacade();
    }

    protected function createChmodFacade(): ChmodFacade
    {
        return new ChmodFacade();
    }

    protected function createStatToSplFileInfoConverter()
    {
        return new StatToSplFileInfo(
            $this->createChmodFacade()
        );
    }

    protected function createResponseDecoratorContainer(): ResponseDecoratorContainer
    {
        return new ResponseDecoratorContainer(
            $this->createVisibility()
        );
    }

    protected function createVisibility(): VisibilityPermissionConverter
    {
        return new VisibilityPermissionConverter();
    }
}
