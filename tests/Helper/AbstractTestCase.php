<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Helper;

use League\Flysystem\AdapterInterface;
use PHPUnit\Framework\TestCase;
use Phuxtil\Flysystem\SshShell\SshShellConfigurator;
use Phuxtil\Flysystem\SshShell\SshShellFactory;

abstract class AbstractTestCase extends TestCase
{
    const LOCAL_PATH = \TESTS_FIXTURE_DIR . 'local_fs/';
    const LOCAL_FILE = self::LOCAL_PATH . 'test/local.txt';
    const LOCAL_NAME = 'test/local.txt';
    //
    const REMOTE_PATH = '/tmp/remote_fs/';
    const REMOTE_FILE = self::REMOTE_PATH . 'remote.txt';
    const REMOTE_FILE_LINK = self::REMOTE_PATH . 'remote_link.txt';
    const REMOTE_NAME = '/remote.txt';
    const REMOTE_PATH_NAME = '/';
    //
    const REMOTE_NEWPATH = self::REMOTE_PATH . 'newpath/';
    const REMOTE_NEWPATH_FILE = self::REMOTE_PATH . 'newpath/remote.txt';
    const REMOTE_NEWPATH_NAME = 'newpath/remote.txt';
    //
    const REMOTE_INVALID_PATH = self::REMOTE_PATH . 'doesnotexist/remote.txt';
    const REMOTE_INVALID_NAME = 'doesnotexist/remote.txt';
    //
    const SSH_USER = \TESTS_SSH_USER;
    const SSH_HOST = \TESTS_SSH_HOST;
    const SSH_PORT = \TESTS_SSH_PORT;
    const SSH_KEY = \TESTS_SSH_KEY;

    /**
     * @var \Phuxtil\Flysystem\SshShell\SshShellConfigurator
     */
    protected $configurator;

    /**
     * @var SshShellFactory
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->cleanup();

        $this->configurator = (new SshShellConfigurator())
            ->setRoot(static::REMOTE_PATH)
            ->setUser(static::SSH_USER)
            ->setHost(static::SSH_HOST)
            ->setPort(static::SSH_PORT)
            ->setPrivateKey(static::SSH_KEY);

        $this->factory = new SshShellFactory();
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

    protected function setupRemoteFile()
    {
        @mkdir(\dirname(static::REMOTE_FILE), 0777, true);

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

    protected function assertFileResult(
        array $result,
        string $visisibility = AdapterInterface::VISIBILITY_PUBLIC,
        $permissions = '0644'
    ) {
        $this->assertEquals($result['visibility'], $visisibility);
        $this->assertEquals($result['type'], 'file');
        $this->assertEquals($result['perms'], $permissions);

        $this->assertFalse($result['link']);
        $this->assertFalse($result['dir']);
        $this->assertTrue($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertFalse($result['executable']);
    }

    protected function assertDirResult(
        array $result,
        string $visisibility = AdapterInterface::VISIBILITY_PUBLIC,
        $permissions = '0755'
    ) {
        $this->assertEquals($result['visibility'], $visisibility);
        $this->assertEquals($result['type'], 'dir');
        $this->assertEquals($result['perms'], $permissions);

        $this->assertFalse($result['link']);
        $this->assertTrue($result['dir']);
        $this->assertFalse($result['file']);
        $this->assertTrue($result['writable']);
        $this->assertTrue($result['readable']);
        $this->assertTrue($result['executable']);
    }

    protected function assertContent(string $contentFile = self::REMOTE_NEWPATH_FILE)
    {
        $this->assertFileExists($contentFile);

        $this->assertEquals(
            \file_get_contents(static::LOCAL_FILE),
            \file_get_contents($contentFile)
        );
    }

    protected function assertPathInfo(
        array $result,
        string $path = self::REMOTE_NEWPATH,
        string $pathname = self::REMOTE_NEWPATH_FILE
    ) {
        $this->assertEquals($result['path'] . '/', $path);
        $this->assertEquals($result['pathname'], $pathname);
    }
}
