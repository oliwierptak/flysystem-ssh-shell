<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use SplFileInfo;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class AdapterReaderTest extends AbstractTestCase
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Adapter\SshShellAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        parent::setUp();

        $this->adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $this->setupRemoteFile();
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

    public function test_getMetadata_should_return_false()
    {
        $metadata = $this->adapter->getMetadata(static::REMOTE_INVALID_NAME);

        $this->assertFalse($metadata);
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
        $result = $this->adapter->read(static::REMOTE_NAME);

        $this->assertEquals(
            \file_get_contents(static::REMOTE_FILE),
            $result['contents']
        );
    }

    public function test_read_should_return_false_when_ssh_command_fails()
    {
        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter($this->configurator);

        $result = $adapter->read(static::REMOTE_NAME);

        $this->assertFalse($result);
    }

    public function test_read_should_return_false_when_invalid_path()
    {
        $result = $this->adapter->read(static::REMOTE_INVALID_NAME);

        $this->assertFalse($result);
    }

    public function test_listContents()
    {
        $result = $this->adapter->listContents(static::REMOTE_PATH_NAME);

        foreach ($result as $item) {
            $expected = new \SplFileInfo($item['pathname']);
            $info = (new VirtualSplFileInfo($item['pathname']))
                ->fromArray($item);

            $this->assertOutput($expected, $info);
        }
    }

    public function test_listContents_should_return_empty_array_when_ssh_command_fails()
    {
        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter($this->configurator);

        $result = $adapter->listContents(static::REMOTE_PATH_NAME);

        $this->assertCount(0, $result);
    }

    public function test_listContents_recursively()
    {
        $result = $this->adapter->listContents(static::REMOTE_PATH_NAME, true);

        foreach ($result as $item) {
            $expected = new \SplFileInfo($item['pathname']);
            $info = (new VirtualSplFileInfo($item['pathname']))
                ->fromArray($item);

            $this->assertOutput($expected, $info);
        }
    }

    protected function assertOutput(\SplFileInfo $expected, \SplFileInfo $info)
    {
        $octal = substr(sprintf('%o', fileperms($expected->getPathname())), -4);

        //links are resolved by find, however fileperms() and filetype() will return link info
        if ($expected->isLink()) {
            $linkTargetInfo = new SplFileInfo($expected->getRealPath());
            $this->assertEquals($linkTargetInfo->getType(), $info->getType());
            $this->assertEquals($linkTargetInfo->isLink(), $info->isLink());
            $this->assertEquals($expected->getPathname(), $info->getRealPath());
        }
        else {
            $this->assertEquals($expected->getType(), $info->getType());
            $this->assertEquals($expected->isLink(), $info->isLink());
            $this->assertEquals($expected->getRealPath(), $info->getRealPath());
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
