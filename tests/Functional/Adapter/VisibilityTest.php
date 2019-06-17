<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class VisibilityTest extends AbstractTestCase
{
    public function test_setVisibility_to_private()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_NAME, AdapterInterface::VISIBILITY_PRIVATE);

        $this->assertPathInfo($result, static::REMOTE_PATH, static::REMOTE_FILE);
        $this->assertFileResult($result, AdapterInterface::VISIBILITY_PRIVATE, '0600');
        $this->assertContent(static::REMOTE_FILE);
    }

    public function test_setVisibility_to_public()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_NAME, AdapterInterface::VISIBILITY_PUBLIC);

        $this->assertPathInfo($result, static::REMOTE_PATH, static::REMOTE_FILE);
        $this->assertFileResult($result, AdapterInterface::VISIBILITY_PUBLIC, '0644');
        $this->assertContent(static::REMOTE_FILE);
    }

    public function test_setVisibility_of_folder_to_private()
    {
        @mkdir(static::REMOTE_NEWPATH, 0777, true);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility('/newpath', AdapterInterface::VISIBILITY_PRIVATE);

        @rmdir(static::REMOTE_NEWPATH);

        $this->assertPathInfo($result, static::REMOTE_PATH, rtrim(static::REMOTE_NEWPATH, '/'));
        $this->assertDirResult($result, AdapterInterface::VISIBILITY_PRIVATE, '0700');
    }

    public function skip_test_setVisibility_to_of_folder_public()
    {
        @mkdir(static::REMOTE_NEWPATH, 0777, true);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility('/newpath', AdapterInterface::VISIBILITY_PUBLIC);

        @rmdir(static::REMOTE_NEWPATH);

        $this->assertPathInfo($result, static::REMOTE_PATH, rtrim(static::REMOTE_NEWPATH, '/'));
        $this->assertDirResult($result);
    }

    public function test_setVisibility_invalid()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_NAME, 'invalid');

        $this->assertFalse($result);
    }

    public function test_setVisibility_invalid_path()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_INVALID_NAME, AdapterInterface::VISIBILITY_PUBLIC);

        $this->assertFalse($result);
    }
}
