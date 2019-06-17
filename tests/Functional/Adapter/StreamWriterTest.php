<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class StreamWriterTest extends AbstractTestCase
{
    public function test_writeStream_should_create_path()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertPathInfo($result);
        $this->assertFileResult($result);
        $this->assertContent();
    }

    public function test_writeStream_should_set_visibility()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();
        $config->set('visibility', AdapterInterface::VISIBILITY_PRIVATE);

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertPathInfo($result);
        $this->assertFileResult($result, AdapterInterface::VISIBILITY_PRIVATE, '0600');
        $this->assertContent();
    }

    public function test_writeStream_should_return_false_when_invalid_resource()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, false, $config);


        $this->assertFalse($result);
    }

    public function test_writeStream_should_return_false_when_ssh_command_fails()
    {
        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertFalse($result);
    }

    public function test_updateStream()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();

        $result = $adapter->updateStream(static::REMOTE_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertPathInfo($result, static::REMOTE_PATH, static::REMOTE_FILE);
        $this->assertFileResult($result);
        $this->assertContent(static::REMOTE_FILE);
    }

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
}
