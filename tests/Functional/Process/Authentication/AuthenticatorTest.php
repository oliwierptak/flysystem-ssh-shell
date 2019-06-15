<?php

namespace TestsFlysystemSshShell\Functional\Process\Authentication;

use League\Flysystem\SshShell\SshShellConfigurator;
use League\Flysystem\SshShell\Process\Authentication\Authenticator;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase
{
    public function test_generate_by_config()
    {
        $configurator = (new SshShellConfigurator())
            ->setUser('root')
            ->setHost('pup-data-container');

        $authenticator = new Authenticator();
        $auth = $authenticator->generate($configurator);

        $this->assertEquals('', $auth);
    }

    public function test_generate_by_private_key()
    {
        $configurator = (new SshShellConfigurator())
            ->setPrivateKey('~/.ssh/id_rsa.data_container');

        $authenticator = new Authenticator();
        $auth = $authenticator->generate($configurator);

        $this->assertEquals('-i ~/.ssh/id_rsa.data_container', $auth);
    }
}
