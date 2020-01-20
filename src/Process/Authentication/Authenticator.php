<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Process\Authentication;

use Phuxtil\Flysystem\SshShell\Process\Authentication\Type\PrivateKey;
use Phuxtil\Flysystem\SshShell\SshShellConfigurator;

class Authenticator
{
    protected $authenticationTypes = [
        Type\Config::TYPE => Type\Config::class,
        Type\PrivateKey::TYPE => Type\PrivateKey::class,
    ];

    /**
     * @param \Phuxtil\Flysystem\SshShell\SshShellConfigurator $configurator
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
     * @param \Phuxtil\Flysystem\SshShell\SshShellConfigurator $configurator
     *
     * @return AbstractAuthentication
     * @throws \InvalidArgumentException
     */
    protected function getAuthByType(SshShellConfigurator $configurator): AbstractAuthentication
    {
        if (!isset($this->authenticationTypes[$configurator->requireAuthType()])) {
            throw new \InvalidArgumentException('Unknown authentication type: ' . $configurator->requireAuthType());
        }

        if (trim($configurator->requirePrivateKey()) !== '') {
            $configurator->setAuthType(PrivateKey::TYPE);
        }

        $className = $this->authenticationTypes[$configurator->requireAuthType()];

        /** @var \Phuxtil\Flysystem\SshShell\Process\Authentication\AbstractAuthentication $auth */
        return new $className($configurator);
    }
}
