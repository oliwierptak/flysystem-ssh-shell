<?php

namespace Phuxtil\Flysystem\SshShell;

class SshShellConfigurator
{
    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $privateKey = '';

    /**
     * @var string
     */
    protected $authType = 'config';

    /**
     * @var string
     */
    protected $root = '/tmp';

    /**
     * @var int
     */
    protected $port = 22;

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): SshShellConfigurator
    {
        $this->user = $user;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): SshShellConfigurator
    {
        $this->host = $host;

        return $this;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(string $privateKey): SshShellConfigurator
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function getAuthType(): string
    {
        return $this->authType;
    }

    public function setAuthType(string $authType): SshShellConfigurator
    {
        $this->authType = $authType;

        return $this;
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function setRoot(string $root): SshShellConfigurator
    {
        $this->root = $root;

        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): SshShellConfigurator
    {
        $this->port = $port;

        return $this;
    }
}
