<?php

namespace Phuxtil\Flysystem\SshShell\Process;

use Phuxtil\Flysystem\SshShell\SshShellConfigurator;
use Phuxtil\Flysystem\SshShell\Process\Authentication\Authenticator;
use Symfony\Component\Process\Process;

class Ssh
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Process\Authentication\Authenticator
     */
    protected $authenticator;

    /**
     * @var \Phuxtil\Flysystem\SshShell\SshShellConfigurator
     */
    protected $configurator;

    public function __construct(
        Authenticator $authenticator,
        SshShellConfigurator $configurator
    ) {
        $this->authenticator = $authenticator;
        $this->configurator = $configurator;
    }

    public function execute(string $commandPattern, array $arguments): Process
    {
        $userAtHost = $this->getUserAtHost();
        $commandPattern = \str_replace('<USER_AT_HOST>', $userAtHost, $commandPattern);

        $command = vsprintf($commandPattern, $arguments);
        $process = $this->runProcess($command);

        return $process;
    }

    protected function runProcess(string $command): Process
    {
        $command = $this->prepareCommand($command);

        $process = Process::fromShellCommandline($command);
        $process->run();
        $process->wait();

        return $process;
    }

    protected function prepareCommand(string $command): string
    {
        $command = sprintf(
            'ssh -p %d -l %s %s %s "%s"',
            $this->configurator->requirePort(),
            $this->configurator->requireUser(),
            $this->prepareAuth(),
            $this->getUserAtHost(),
            $command
        );

        return $command;
    }

    protected function prepareAuth(): string
    {
        return $this->authenticator->generate($this->configurator);
    }

    public function getUserAtHost(): string
    {
        return sprintf(
            '%s@%s',
            $this->configurator->requireUser(),
            $this->configurator->requireHost()
        );
    }
}
