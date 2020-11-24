<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Acceptance\Adapter;

use League\Flysystem\Config;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

/**
 * @group flysystem-ssh-shell
 * @group acceptance
 */
class TimeoutTest extends AbstractTestCase
{
    public function test_timeout_default()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
    }

    public function test_timeout_disabled_with_0()
    {
        $this->setupRemoteFile();

        $this->configurator->setTimeout(0);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
    }

    public function test_timeout_disabled_with_null()
    {
        $this->setupRemoteFile();

        $this->configurator->setTimeout(null);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
    }
}
