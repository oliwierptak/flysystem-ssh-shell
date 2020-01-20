<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Acceptance\Filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

/**
 * @group flysystem-ssh-shell
 * @group acceptance
 * @group filesystem
 */
class FilesystemReaderTest extends AbstractTestCase
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter($this->configurator);
        $this->filesystem = new Filesystem($adapter);
    }

    public function test_has()
    {
        $has = $this->filesystem->has(static::REMOTE_NAME);
        $hasNot = $this->filesystem->has(static::REMOTE_NEWPATH);

        $this->assertTrue($has);
        $this->assertFalse($hasNot);
    }

    public function test_readStream()
    {
        $stream = $this->filesystem->readStream(static::REMOTE_NAME);

        $this->assertIsResource($stream);

        $content = \stream_get_contents($stream);

        $this->assertEquals($content, \file_get_contents(static::REMOTE_FILE));
    }

    public function test_listContents()
    {
        $content = $this->filesystem->listContents(static::REMOTE_PATH_NAME, true);

        $this->assertNotEmpty($content);
        $this->assertCount(3, $content);

        $fileInfo = $content[1];

        $this->assertEquals($fileInfo['type'], 'file');
        $this->assertEquals($fileInfo['path'], 'remote.txt');
        $this->assertNotEmpty($fileInfo['timestamp']);
        $this->assertEquals($fileInfo['size'], 70);
        $this->assertEquals($fileInfo['dirname'], '/');
        $this->assertEquals($fileInfo['basename'], 'remote.txt');
        $this->assertEquals($fileInfo['extension'], 'txt');
        $this->assertEquals($fileInfo['filename'], 'remote');
    }

    public function test_getMimetype()
    {
        $mimeType = $this->filesystem->getMimetype(static::REMOTE_NAME);

        $this->assertEquals('text/plain', $mimeType);
    }

    public function test_getTimestamp()
    {
        $expected = time();
        touch(static::REMOTE_FILE, $expected);

        $timestamp = $this->filesystem->getTimestamp(static::REMOTE_NAME);

        $this->assertEquals($expected, $timestamp);
    }

    public function test_getVisibility()
    {
        $visibility = $this->filesystem->getVisibility(static::REMOTE_NAME);

        $this->assertEquals(AdapterInterface::VISIBILITY_PUBLIC, $visibility);
    }

    public function test_getSize()
    {
        $size = $this->filesystem->getSize(static::REMOTE_NAME);

        $this->assertEquals(70, $size);
    }

    public function test_getMetdata()
    {
        $expectedTimestamp = time();
        touch(static::REMOTE_FILE, $expectedTimestamp);

        $metadata = $this->filesystem->getMetadata(static::REMOTE_NAME);

        $this->assertEquals($expectedTimestamp, $metadata['timestamp']);
        $this->assertEquals(AdapterInterface::VISIBILITY_PUBLIC, $metadata['visibility']);
        $this->assertEquals('text/plain', $metadata['mimetype']);

        $this->assertEquals('remote.txt', $metadata['path']);
        $this->assertEquals('remote', $metadata['filename']);
        $this->assertEquals('remote.txt', $metadata['basename']);
        $this->assertEquals('txt', $metadata['extension']);
        $this->assertEquals(static::REMOTE_FILE, $metadata['realPath']);
        $this->assertEquals(70, $metadata['size']);
        $this->assertEquals('0644', $metadata['perms']);
    }
}
