<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Phuxtil\Flysystem\SshShell\SshShellConfigurator;
use Phuxtil\Flysystem\SshShell\SshShellFactory;
use PHPUnit\Framework\TestCase;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class AdapterWriterTest extends TestCase
{
    const LOCAL_PATH = \TESTS_FIXTURE_DIR . 'local_fs/';
    const LOCAL_FILE = self::LOCAL_PATH . 'test/local.txt';
    const LOCAL_NAME = 'test/local.txt';
    const REMOTE_PATH = '/tmp/remote_fs/';
    const REMOTE_FILE = self::REMOTE_PATH . 'remote.txt';
    const REMOTE_FILE_LINK = self::REMOTE_PATH . 'remote_link.txt';
    const REMOTE_NAME = '/remote.txt';
    const REMOTE_NEWPATH = self::REMOTE_PATH . 'newpath/';
    const REMOTE_NEWPATH_FILE = self::REMOTE_PATH . 'newpath/remote.txt';
    const REMOTE_NEWPATH_NAME = 'newpath/remote.txt';
    const REMOTE_INVALID_PATH = self::REMOTE_PATH . 'doesnotexist/remote.txt';
    const REMOTE_INVALID_NAME = 'doesnotexist/remote.txt';

    const SSH_USER = \TESTS_SSH_USER;
    const SSH_HOST = \TESTS_SSH_HOST;
    const SSH_PORT = \TESTS_SSH_PORT;

    /**
     * @var \Phuxtil\Flysystem\SshShell\SshShellConfigurator
     */
    protected $configurator;

    /**
     * @var SshShellFactory
     */
    protected $factory;

    /**
     * @var VirtualSplFileInfo
     */
    protected $expectedFileInfo;

    protected function setUp()
    {
        $this->cleanup();

        $this->configurator = (new SshShellConfigurator())
            ->setRoot(static::REMOTE_PATH)
            ->setUser(static::SSH_USER)
            ->setHost(static::SSH_HOST)
            ->setPort(static::SSH_PORT);

        $this->factory = new SshShellFactory();
    }

    protected function setupRemoteFile()
    {
        @mkdir(\dirname(static::REMOTE_FILE), 0777, true);

        \file_put_contents(
            static::REMOTE_FILE,
            \file_get_contents(static::LOCAL_FILE)
        );

        \symlink(static::REMOTE_FILE, static::REMOTE_FILE_LINK);
    }

    protected function setupLocalFile()
    {
        \file_put_contents(
            static::REMOTE_FILE,
            \file_get_contents(static::LOCAL_FILE)
        );

        \symlink(static::REMOTE_FILE, static::REMOTE_FILE_LINK);
    }

    protected function setupRemoteTempFile(): string
    {
        $filename = static::REMOTE_PATH . time() . 'file.txt';
        @mkdir(\dirname($filename), 0777, true);

        \file_put_contents(
            $filename,
            \file_get_contents(static::LOCAL_FILE)
        );

        return $filename;
    }

    protected function setupRemoteTempDir(): string
    {
        $dir = static::REMOTE_PATH . time();
        @mkdir($dir, 0777, true);

        return $dir;
    }

    protected function tearDown()
    {
        $this->cleanup();
    }

    protected function cleanup()
    {
        @unlink(static::REMOTE_FILE);
        @unlink(static::REMOTE_FILE_LINK);
        @unlink(static::REMOTE_NEWPATH_FILE);
        @unlink(static::REMOTE_INVALID_PATH);

        @rmdir(
            dirname(
                static::REMOTE_FILE
            )
        );

        @rmdir(
            dirname(
                static::REMOTE_NEWPATH_FILE
            )
        );
    }

    public function test_write_should_create_path()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertEquals($result['visibility'], AdapterInterface::VISIBILITY_PRIVATE);
        $this->assertEquals($result['path'] . '/', static::REMOTE_NEWPATH);
        $this->assertEquals($result['pathname'], static::REMOTE_NEWPATH_FILE);
        $this->assertEquals($result['type'], 'file');
        $this->assertEquals($result['perms'], '0600');

        $this->assertFalse($result['link']);
        $this->assertFalse($result['dir']);
        $this->assertTrue($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertfalse($result['executable']);

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

        $this->assertEquals($result['visibility'], AdapterInterface::VISIBILITY_PRIVATE);
        $this->assertEquals($result['path'] . '/', static::REMOTE_NEWPATH);
        $this->assertEquals($result['pathname'], static::REMOTE_NEWPATH_FILE);
        $this->assertEquals($result['type'], 'file');
        $this->assertEquals($result['perms'], '0600');

        $this->assertFalse($result['link']);
        $this->assertFalse($result['dir']);
        $this->assertTrue($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertfalse($result['executable']);

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

    public function test_writeStream_should_set_visibility()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $config->set('visibility', AdapterInterface::VISIBILITY_PUBLIC);
        $result = $adapter->write(static::REMOTE_NEWPATH_NAME, 'FooBaroo', $config);

        $this->assertEquals($result['visibility'], AdapterInterface::VISIBILITY_PUBLIC);
        $this->assertEquals($result['path'] . '/', static::REMOTE_NEWPATH);
        $this->assertEquals($result['pathname'], static::REMOTE_NEWPATH_FILE);
        $this->assertEquals($result['type'], 'file');
        $this->assertEquals($result['perms'], '0644');

        $this->assertFalse($result['link']);
        $this->assertFalse($result['dir']);
        $this->assertTrue($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertFalse($result['executable']);

        $this->assertEquals('FooBaroo', \file_get_contents(static::REMOTE_NEWPATH_FILE));
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
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

    public function test_update_should_not_change_meta()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->update(static::REMOTE_NAME, 'FooBaroo', $config);

        $this->assertEquals($result['visibility'], AdapterInterface::VISIBILITY_PUBLIC);
        $this->assertEquals($result['path'] . '/', static::REMOTE_PATH);
        $this->assertEquals($result['pathname'], static::REMOTE_FILE);
        $this->assertEquals($result['type'], 'file');
        $this->assertEquals($result['perms'], '0644');

        $this->assertFalse($result['link']);
        $this->assertFalse($result['dir']);
        $this->assertTrue($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertFalse($result['executable']);

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

    public function test_rename()
    {
        $fileToMove = $this->setupRemoteTempFile();
        $fileToMoveName = \basename($fileToMove);
        $this->assertFileNotExists(static::REMOTE_NEWPATH_FILE);

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->rename($fileToMoveName, static::REMOTE_NEWPATH_NAME);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertFileNotExists($fileToMoveName);
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
