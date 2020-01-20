<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Functional\Filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class FilesystemWriterTest extends AbstractTestCase
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

    public function test_write()
    {
        $result = $this->filesystem->write(static::REMOTE_NEWPATH_NAME, 'Lorem Ipsum');

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertEquals('Lorem Ipsum', \file_get_contents(static::REMOTE_NEWPATH_FILE));
    }

    public function test_writeStream()
    {
        $stream = \fopen(static::LOCAL_FILE, 'r');

        $result = $this->filesystem->writeStream(static::REMOTE_NEWPATH_NAME, $stream);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
        $this->assertEquals(\file_get_contents(static::LOCAL_FILE), \file_get_contents(static::REMOTE_NEWPATH_FILE));
    }

    public function test_put()
    {
        $result = $this->filesystem->put(static::REMOTE_NAME, 'Lorem Ipsum');

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_FILE);
        $this->assertEquals('Lorem Ipsum', \file_get_contents(static::REMOTE_FILE));
    }

    public function test_putStream()
    {
        $stream = \fopen(static::LOCAL_FILE, 'r');

        $result = $this->filesystem->putStream(static::REMOTE_NAME, $stream);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_FILE);
        $this->assertEquals(\file_get_contents(static::LOCAL_FILE), \file_get_contents(static::REMOTE_FILE));
    }

    public function test_readAndDelete()
    {
        $content = $this->filesystem->readAndDelete(static::REMOTE_NAME);

        $this->assertFileNotExists(static::REMOTE_FILE);
        $this->assertEquals($content, \file_get_contents(static::LOCAL_FILE));
    }

    public function test_update()
    {
        $result = $this->filesystem->update(static::REMOTE_NAME, 'Lorem Ipsum');

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_FILE);
        $this->assertEquals('Lorem Ipsum', \file_get_contents(static::REMOTE_FILE));
    }

    public function test_updateStream()
    {
        $stream = \fopen(static::LOCAL_FILE, 'r');

        $result = $this->filesystem->updateStream(static::REMOTE_NAME, $stream);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_FILE);
        $this->assertEquals(\file_get_contents(static::LOCAL_FILE), \file_get_contents(static::REMOTE_FILE));
    }

    public function test_rename()
    {
        $result = $this->filesystem->rename(static::REMOTE_NAME, static::REMOTE_NEWPATH_NAME);

        $this->assertTrue($result);
        $this->assertFileNotExists(static::REMOTE_FILE);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_copy()
    {
        $result = $this->filesystem->copy(static::REMOTE_NAME, static::REMOTE_NEWPATH_NAME);

        $this->assertTrue($result);
        $this->assertFileExists(static::REMOTE_NEWPATH_FILE);
    }

    public function test_delete()
    {
        $result = $this->filesystem->delete(static::REMOTE_NAME);

        $this->assertTrue($result);
        $this->assertFileNotExists(static::REMOTE_FILE);
    }

    public function test_deleteDir()
    {
        $this->filesystem->createDir('/newpath/');
        $result = $this->filesystem->deleteDir('/newpath/');

        $this->assertTrue($result);
        $this->assertDirectoryNotExists(static::REMOTE_NEWPATH);
    }

    public function test_createDir()
    {
        $result = $this->filesystem->createDir('/newpath/');

        $this->assertTrue($result);
        $this->assertDirectoryExists(static::REMOTE_NEWPATH);
    }

    public function test_setVisibility()
    {
        $result = $this->filesystem->setVisibility(static::REMOTE_NAME, AdapterInterface::VISIBILITY_PRIVATE);

        $visibility = $this->filesystem->getVisibility(static::REMOTE_NAME);

        $this->assertTrue($result);
        $this->assertEquals(AdapterInterface::VISIBILITY_PRIVATE, $visibility);
    }
}
