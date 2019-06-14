<?php

namespace League\Flysystem\SshShell\Process\Authentication;

use League\Flysystem\SshShell\Configurator;

class Authenticator
{
    protected $authenticationTypes = [
        Type\Config::TYPE => Type\Config::class,
        Type\PrivateKey::TYPE => Type\PrivateKey::class,
    ];

    /**
     * @param \League\Flysystem\SshShell\Configurator $configurator
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generate(Configurator $configurator): string
    {
        $auth = $this->getAuthByType($configurator);

        return $auth->generate();
    }

    /**
     * @param \League\Flysystem\SshShell\Configurator $configurator
     *
     * @return AbstractAuthentication
     * @throws \InvalidArgumentException
     */
    protected function getAuthByType(Configurator $configurator): AbstractAuthentication
    {
        if (!isset($this->authenticationTypes[$configurator->getAuthType()])) {
            throw new \InvalidArgumentException('Unknown authentication type: ' . $configurator->getAuthType());
        }

        $className = $this->authenticationTypes[$configurator->getAuthType()];

        /** @var \League\Flysystem\SshShell\Process\Authentication\AbstractAuthentication $auth */
        return new $className($configurator);
    }
}
