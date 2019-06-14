<?php

namespace TestsFlysystemSshShell\Functional\Process\Authentication;

use League\Flysystem\SshShell\Configurator;
use League\Flysystem\SshShell\Process\Authentication\Authenticator;
use League\Flysystem\SshShell\Process\Authentication\Type\Config;
use League\Flysystem\SshShell\Process\Authentication\Type\PrivateKey;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase
{
    public function test_generate_by_config()
    {
        $configurator = (new Configurator())
            ->setUser('root')
            ->setHost('pup-data-container')
            ->setAuthType(Config::TYPE);

        $authenticator = new Authenticator();
        $auth = $authenticator->generate($configurator);

        $this->assertEquals('', $auth);
    }

    public function test_generate_by_private_key()
    {
        $configurator = (new Configurator())
            ->setPrivateKey('~/.ssh/id_rsa.data_container')
            ->setAuthType(PrivateKey::TYPE);

        $authenticator = new Authenticator();
        $auth = $authenticator->generate($configurator);

        $this->assertEquals('-i ~/.ssh/id_rsa.data_container', $auth);
    }
}
