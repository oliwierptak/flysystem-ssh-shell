<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Acceptance\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

/**
 * @group flysystem-ssh-shell
 * @group acceptance
 * @group adapter
 * @group writer
 */
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

        $expected = [
            'contents' => 'FooBaroo',
            'type' => 'file',
            'size' => strlen('FooBaroo'),
            'path' => static::REMOTE_NEWPATH_NAME,
            'visibility' => AdapterInterface::VISIBILITY_PRIVATE,
        ];

        $this->assertEquals($expected, $result);
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

        $expected = [
            'contents' => 'FooBaroo',
            'type' => 'file',
            'size' => strlen('FooBaroo'),
            'path' => static::REMOTE_NEWPATH_NAME,
        ];

        $this->assertEquals($expected, $result);
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

        $expected = [
            'contents' => 'FooBaroo',
            'type' => 'file',
            'size' => strlen('FooBaroo'),
            'path' => static::REMOTE_NEWPATH_NAME,
        ];

        $this->assertEquals($expected, $result);
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

        $expected = [
            'contents' => 'FooBaroo',
            'type' => 'file',
            'size' => strlen('FooBaroo'),
            'path' => static::REMOTE_NAME,
        ];

        $this->assertEquals($expected, $result);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_FILE));
        $this->assertFileExists(static::REMOTE_FILE);
    }

    public function test_update_should_change_visibility()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $config->set('visibility', AdapterInterface::VISIBILITY_PRIVATE);
        $result = $adapter->update(static::REMOTE_NAME, 'FooBaroo', $config);

        $expected = [
            'contents' => 'FooBaroo',
            'type' => 'file',
            'size' => strlen('FooBaroo'),
            'path' => static::REMOTE_NAME,
            'visibility' => AdapterInterface::VISIBILITY_PRIVATE,
        ];

        $this->assertEquals($expected, $result);
        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_FILE));
        $this->assertFileExists(static::REMOTE_FILE);
    }

    public function test_update_should_return_false_when_path_does_not_exist()
    {
        $this->assertFileDoesNotExist(static::REMOTE_INVALID_PATH);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->update(static::REMOTE_INVALID_NAME, 'FooBaroo', $config);

        $this->assertFalse($result);
        $this->assertFileDoesNotExist(static::REMOTE_INVALID_PATH);
    }

    public function test_mkdir()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->createDir('/newpath/', $config);

        $expected = [
            'path' => '/newpath/',
            'type' => 'dir',
        ];

        $this->assertEquals($expected, $result);
        $this->assertDirectoryExists(static::REMOTE_NEWPATH);
    }

    public function test_copy()
    {
        $this->setupRemoteFile();
        $this->assertFileDoesNotExist(static::REMOTE_NEWPATH_FILE);

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
        $this->assertFileDoesNotExist(static::REMOTE_NEWPATH_FILE);

        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->copy(static::REMOTE_NAME, static::REMOTE_NEWPATH_NAME);

        $this->assertFalse($result);
        $this->assertFileDoesNotExist(static::REMOTE_NEWPATH_FILE);
    }

    public function test_rename()
    {
        $this->setupRemoteFile();

        $fileToMove = $this->setupRemoteTempFile();
        $fileToMoveName = \basename($fileToMove);
        $this->assertFileDoesNotExist(static::REMOTE_NEWPATH_FILE);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->rename($fileToMoveName, static::REMOTE_NEWPATH_NAME);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertFileDoesNotExist($fileToMove);
    }

    public function test_rename_should_return_false_when_ssh_process_fails()
    {
        $fileToMove = $this->setupRemoteTempFile();
        $fileToMoveName = \basename($fileToMove);

        $this->assertFileDoesNotExist(static::REMOTE_NEWPATH_FILE);

        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->rename($fileToMoveName, static::REMOTE_NEWPATH_NAME);

        $this->assertFalse($result);
        $this->assertFileExists($fileToMove);
        $this->assertFileDoesNotExist(static::REMOTE_NEWPATH_FILE);
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
        $this->assertFileDoesNotExist($fileToMoveName);
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
        $this->assertDirectoryDoesNotExist($dirName);
    }
}
