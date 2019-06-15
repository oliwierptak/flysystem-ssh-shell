<?php

namespace TestsFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\SshShell\SshShellConfigurator;
use League\Flysystem\SshShell\SshShellFactory;
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
    const REMOTE_PATH_NAME = '/';

    /**
     * @var \League\Flysystem\SshShell\SshShellConfigurator
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

    /**
     * @var \League\Flysystem\SshShell\Adapter\SshShellAdapter
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

        $this->configurator = (new SshShellConfigurator())
            ->setRoot(static::REMOTE_PATH)
            ->setUser('root')
            ->setHost('pup-data-container');

        $this->factory = new SshShellFactory();
        $this->adapter = $this->factory->createAdapter(
            $this->configurator
        );

        //re-create remote file so we can assert and return it from ssh calls
        $this->setupRemoteFile();

        \clearstatcache(true, static::REMOTE_PATH);
        \clearstatcache(true, static::REMOTE_FILE);
        \clearstatcache(true, static::REMOTE_FILE_LINK);
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

    public function test_listContents()
    {
        $result = $this->adapter->listContents(static::REMOTE_PATH_NAME);

        foreach ($result as $output) {
            /** @var \SplFileInfo $output */
            $expected = new \SplFileInfo($output->getPathname());
            $this->assertOutput($expected, $output);
        }
    }

    public function test_listContents_recursively()
    {
        $result = $this->adapter->listContents(static::REMOTE_PATH_NAME, true);

        foreach ($result as $output) {
            /** @var \SplFileInfo $output */
            $expected = new \SplFileInfo($output->getPathname());
            $this->assertOutput($expected, $output);
        }
    }

    protected function assertOutput(\SplFileInfo $expected, \SplFileInfo $info)
    {
        $octal = substr(sprintf('%o', fileperms($expected->getPathname())), -4);

        //links are resolved by find, however fileperms() and filetype() will return link info
        if ($expected->isLink()) {
            $linkTargetInfo = new SplFileInfo($expected->getLinkTarget());
            $this->assertEquals($linkTargetInfo->getType(), $info->getType());
            $this->assertEquals($linkTargetInfo->isLink(), $info->isLink());
        }
        else {
            $this->assertEquals($expected->getType(), $info->getType());
            $this->assertEquals($expected->isLink(), $info->isLink());
        }

        $this->assertEquals($octal, $info->getPerms());
        $this->assertEquals($expected->getOwner(), $info->getOwner());
        $this->assertEquals($expected->getGroup(), $info->getGroup());
        $this->assertEquals($expected->getInode(), $info->getInode());
        $this->assertEquals($expected->getSize(), $info->getSize());
        $this->assertEquals($expected->getFilename(), $info->getFilename());
        $this->assertEquals($expected->getPathname(), $info->getPathname());
        $this->assertEquals($expected->getPath(), $info->getPath());
        $this->assertEquals($expected->getBasename(), $info->getBasename());
        $this->assertEquals($expected->getExtension(), $info->getExtension());
        $this->assertEquals($expected->getRealPath(), $info->getRealPath());
        $this->assertLessThanOrEqual($expected->getATime(), $info->getATime());
        $this->assertEquals($expected->getMTime(), $info->getMTime());
        $this->assertEquals($expected->getCTime(), $info->getCTime());
        $this->assertEquals($expected->isFile(), $info->isFile());
        $this->assertEquals($expected->isDir(), $info->isDir());
        $this->assertEquals($expected->isReadable(), $info->isReadable());
        $this->assertEquals($expected->isWritable(), $info->isWritable());
        $this->assertEquals($expected->isExecutable(), $info->isExecutable());
    }
}
