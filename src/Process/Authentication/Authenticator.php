<?php

namespace League\Flysystem\SshShell\Process\Authentication;

use League\Flysystem\SshShell\Process\Authentication\Type\PrivateKey;
use League\Flysystem\SshShell\SshShellConfigurator;

class Authenticator
{
    protected $authenticationTypes = [
        Type\Config::TYPE => Type\Config::class,
        Type\PrivateKey::TYPE => Type\PrivateKey::class,
    ];

    /**
     * @param \League\Flysystem\SshShell\SshShellConfigurator $configurator
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generate(SshShellConfigurator $configurator): string
    {
        $auth = $this->getAuthByType($configurator);

        return $auth->generate();
    }

    /**
     * @param \League\Flysystem\SshShell\SshShellConfigurator $configurator
     *
     * @return AbstractAuthentication
     * @throws \InvalidArgumentException
     */
    protected function getAuthByType(SshShellConfigurator $configurator): AbstractAuthentication
    {
        if (!isset($this->authenticationTypes[$configurator->getAuthType()])) {
            throw new \InvalidArgumentException('Unknown authentication type: ' . $configurator->getAuthType());
        }

        if (trim($configurator->getPrivateKey()) !== '') {
            $configurator->setAuthType(PrivateKey::TYPE);
        }

        $className = $this->authenticationTypes[$configurator->getAuthType()];

        /** @var \League\Flysystem\SshShell\Process\Authentication\AbstractAuthentication $auth */
        return new $className($configurator);
    }
}
