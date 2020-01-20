<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Functional\Process\Authentication;

use Phuxtil\Flysystem\SshShell\SshShellConfigurator;
use Phuxtil\Flysystem\SshShell\Process\Authentication\Authenticator;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase
{
    const SSH_USER = \TESTS_SSH_USER;
    const SSH_HOST = \TESTS_SSH_HOST;

    public function test_generate_by_config()
    {
        $configurator = (new SshShellConfigurator())
            ->setUser(static::SSH_HOST)
            ->setHost(static::SSH_USER);

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

    public function test_generate_by_config_should_throw_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown authentication type: invalid');

        $configurator = (new SshShellConfigurator())
            ->setUser(static::SSH_HOST)
            ->setHost(static::SSH_USER)
            ->setAuthType('invalid');

        $authenticator = new Authenticator();
        $authenticator->generate($configurator);
    }
}
