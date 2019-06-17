<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class AdapterWriterTest extends AbstractTestCase
{
    public function test_writeStream_should_set_visibility()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $config->set('visibility', AdapterInterface::VISIBILITY_PRIVATE);

        $result = $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertPathInfo($result);
        $this->assertFileResult($result, AdapterInterface::VISIBILITY_PRIVATE, '0600');
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_write_should_create_path()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertPathInfo($result);
        $this->assertFileResult($result);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_write_should_create_path_with_private_key_auth()
    {
        $this->configurator->setPrivateKey('~/.ssh/id_rsa.data_container');

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertPathInfo($result);
        $this->assertFileResult($result);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_write_should_return_false()
    {
        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertFalse($result);
    }

    public function test_update_should_not_change_meta()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->update(static::REMOTE_NAME, 'FooBaroo', $config);

        $this->assertPathInfo($result, static::REMOTE_PATH, static::REMOTE_FILE);
        $this->assertFileResult($result);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_FILE));
        $this->assertFileExists(static::REMOTE_FILE);
    }

    public function test_update_should_return_false_when_path_does_not_exist()
    {
        $this->assertFileNotExists(static::REMOTE_INVALID_PATH);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->update(static::REMOTE_INVALID_NAME, 'FooBaroo', $config);

        $this->assertFalse($result);
        $this->assertFileNotExists(static::REMOTE_INVALID_PATH);
    }

    public function test_mkdir()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->createDir('/newpath/', $config);

        $this->assertEquals($result['visibility'], AdapterInterface::VISIBILITY_PUBLIC);
        $this->assertEquals($result['path'] . '/', static::REMOTE_PATH);
        $this->assertEquals($result['pathname'] . '/', static::REMOTE_NEWPATH);
        $this->assertEquals($result['type'], 'dir');
        $this->assertEquals($result['perms'], '0755');

        $this->assertFalse($result['link']);
        $this->assertTrue($result['dir']);
        $this->assertFalse($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertTrue($result['executable']);

        $this->assertDirectoryExists(static::REMOTE_NEWPATH);
    }

    public function test_copy()
    {
        $this->setupRemoteFile();
        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->copy(static::REMOTE_NAME, static::REMOTE_NEWPATH_NAME);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_copy_should_return_false_when_ssh_command_fails()
    {
        $this->setupRemoteFile();
        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);

        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->copy(static::REMOTE_NAME, static::REMOTE_NEWPATH_NAME);

        $this->assertFalse($result);
        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_rename()
    {
        $this->setupRemoteFile();

        $fileToMove = $this->setupRemoteTempFile();
        $fileToMoveName = \basename($fileToMove);
        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->rename($fileToMoveName, static::REMOTE_NEWPATH_NAME);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertFileNotExists($fileToMove);
    }

    public function test_rename_should_return_false_when_ssh_process_fails()
    {
        $fileToMove = $this->setupRemoteTempFile();
        $fileToMoveName = \basename($fileToMove);

        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);

        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->rename($fileToMoveName, static::REMOTE_NEWPATH_NAME);

        $this->assertFalse($result);
        $this->assertFileExists($fileToMove);
        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_delete()
    {
        $fileToMove = $this->setupRemoteTempFile();
        $fileToMoveName = \basename($fileToMove);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->delete($fileToMoveName);

        $this->assertTrue($result);
        $this->assertFileNotExists($fileToMoveName);
    }

    public function test_deleteDir()
    {
        $dir = $this->setupRemoteTempDir();
        $dirName = \basename($dir);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->deleteDir($dirName);

        $this->assertTrue($result);
        $this->assertDirectoryNotExists($dirName);
    }
}
