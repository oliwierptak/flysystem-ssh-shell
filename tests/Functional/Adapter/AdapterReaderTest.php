<?php

namespace TestsFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\SshShell\Configurator;
use League\Flysystem\SshShell\SshBashFactory;
use PHPUnit\Framework\TestCase;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use SplFileInfo;

class AdapterReaderTest extends TestCase
{
    const LOCAL_PATH = \TESTS_FIXTURE_DIR . 'local_fs/';
    const REMOTE_PATH = '/tmp/remote_fs/';
    const LOCAL_FILE = self::LOCAL_PATH . 'test/local.txt';
    const REMOTE_FILE = self::REMOTE_PATH . 'remote.txt';
    const REMOTE_FILE_LINK = self::REMOTE_PATH . 'remote_link.txt';
    const REMOTE_NAME = '/remote.txt';

    /**
     * @var \League\Flysystem\SshShell\Configurator
     */
    protected $configurator;

    /**
     * @var SshBashFactory
     */
    protected $factory;

    /**
     * @var VirtualSplFileInfo
     */
    protected $expectedFileInfo;

    /**
     * @var \League\Flysystem\SshShell\Adapter\SshBashAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        @mkdir(static::REMOTE_PATH);
        $this->setupRemoteFile();

        $expectedData = (new VirtualSplFileInfo(static::REMOTE_FILE))
            ->toArray((new SplFileInfo(static::REMOTE_FILE)));

        @unlink(static::REMOTE_FILE);
        $this->expectedFileInfo = (new VirtualSplFileInfo(static::REMOTE_FILE))
            ->fromArray($expectedData);

        $this->configurator = (new Configurator())
            ->setRoot(static::REMOTE_PATH)
            ->setUser('root')
            ->setHost('pup-data-container');

        $this->factory = new SshBashFactory();
        $this->adapter = $this->factory->createAdapter(
            $this->configurator
        );

        //re-create remote file so we can assert and return it from ssh calls
        $this->setupRemoteFile();
    }

    protected function setupRemoteFile()
    {
        @\file_put_contents(
            static::REMOTE_FILE,
            \file_get_contents(static::LOCAL_FILE)
        );

        @\symlink(static::REMOTE_FILE, static::REMOTE_FILE_LINK);
    }

    protected function tearDown()
    {
        @rmdir(
            dirname(
                static::REMOTE_FILE
            )
        );

        @unlink(static::REMOTE_FILE);
        @unlink(static::REMOTE_FILE_LINK);
    }

    public function test_has()
    {
        $this->assertTrue(
            $this->adapter->has(static::REMOTE_NAME)
        );
    }

    public function test_getMetadata()
    {
        $metadata = $this->adapter->getMetadata(static::REMOTE_NAME);
        $expected = [
            'path' => '/tmp/remote_fs',
            'filename' => 'remote.txt',
            'basename' => 'remote.txt',
            'pathname' => '/tmp/remote_fs/remote.txt',
            'extension' => 'txt',
            'realPath' => '/tmp/remote_fs/remote.txt',
            'aTime' => \fileatime(static::REMOTE_FILE),
            'mTime' => \filemtime(static::REMOTE_FILE),
            'cTime' => \filectime(static::REMOTE_FILE),
            'inode' => \fileinode(static::REMOTE_FILE),
            'size' => \filesize(static::REMOTE_FILE),
            'perms' => '0644',
            'owner' => 0,
            'group' => 0,
            'type' => 'file',
            'linkTarget' => -1,
            'writable' => \is_writable(static::REMOTE_FILE),
            'readable' => \is_readable(static::REMOTE_FILE),
            'executable' => \is_executable(static::REMOTE_FILE),
            'file' => \is_file(static::REMOTE_FILE),
            'dir' => \is_dir(static::REMOTE_FILE),
            'link' => \is_link(static::REMOTE_FILE),
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            'timestamp' => \filemtime(static::REMOTE_FILE),
            'mimetype' => 'text/plain',
        ];

        $this->assertEquals(
            $expected,
            $metadata
        );
    }

    public function test_getSize()
    {
        $this->assertEquals(
            \filesize(static::REMOTE_FILE),
            $this->adapter->getSize(static::REMOTE_NAME)['size']
        );
    }

    public function test_getMimetype()
    {
        $this->assertEquals(
            'text/plain',
            $this->adapter->getMimetype(static::REMOTE_NAME)['mimetype']
        );
    }

    public function test_getTimestamp()
    {
        $this->assertEquals(
            filemtime(static::REMOTE_FILE),
            $this->adapter->getTimestamp(static::REMOTE_NAME)['timestamp']
        );
    }

    public function test_getVisibility()
    {
        $this->assertEquals(
            AdapterInterface::VISIBILITY_PUBLIC,
            $this->adapter->getVisibility(static::REMOTE_NAME)['visibility']
        );
    }

    public function test_read()
    {
        $result = $this->adapter->read(static::REMOTE_FILE);

        $this->assertEquals(
            \file_get_contents(static::REMOTE_FILE),
            $result['contents']
        );
    }
}
