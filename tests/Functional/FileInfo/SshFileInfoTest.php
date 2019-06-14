<?php

namespace TestsFlysystemSshShell\Functional\FileInfo;

use League\Flysystem\SshShell\Configurator;
use League\Flysystem\SshShell\FileInfo\SshFileInfo;
use League\Flysystem\SshShell\SshBashFactory;
use PHPUnit\Framework\TestCase;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use Phuxtil\Stat\DefinesInterface as StatDefinesInterface;
use SplFileInfo;

class SshFileInfoTest extends TestCase
{
    const LOCAL_PATH = \TESTS_FIXTURE_DIR . 'local_fs/';
    const REMOTE_PATH =  '/tmp/remote_fs/';
    const LOCAL_FILE = self::LOCAL_PATH . 'test/local.txt';
    const REMOTE_FILE = self::REMOTE_PATH . 'remote.txt';
    const REMOTE_FILE_LINK = self::REMOTE_PATH . 'remote_link.txt';

    /**
     * @var \League\Flysystem\SshShell\Configurator
     */
    protected $configurator;

    /**
     * @var SshBashFactory
     */
    protected $factory;

    /**
     * @var SshFileInfo
     */
    protected $sshFileInfo;

    /**
     * @var VirtualSplFileInfo
     */
    protected $expectedFileInfo;

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
        $this->sshFileInfo = $this->factory->createSshFileInfo(
            static::REMOTE_FILE,
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
        @rmdir(dirname(
            static::REMOTE_FILE
        ));

        @unlink(static::REMOTE_FILE);
        @unlink(static::REMOTE_FILE_LINK);
    }

    public function test_getPath()
    {
        $this->assertEquals(
            static::REMOTE_PATH,
            $this->sshFileInfo->getPath() . \DIRECTORY_SEPARATOR
        );
    }

    public function test_getFilename()
    {
        $this->assertEquals(
            basename(static::REMOTE_FILE),
            $this->sshFileInfo->getFilename()
        );
    }

    public function test_getExtension()
    {
        $this->assertEquals(
            'txt',
            $this->sshFileInfo->getExtension()
        );
    }

    public function test_getBasename()
    {
        $this->assertEquals(
            basename(static::REMOTE_FILE),
            $this->sshFileInfo->getBasename()
        );
    }

    public function test_getPathname()
    {
        $this->assertEquals(
            static::REMOTE_FILE,
            $this->sshFileInfo->getPathname()
        );
    }

    public function test_getATime()
    {
        $this->assertEquals(
            \fileatime(static::REMOTE_FILE),
            $this->sshFileInfo->getATime()
        );
    }

    public function test_getMTime()
    {
        $this->assertEquals(
            \filemtime(static::REMOTE_FILE),
            $this->sshFileInfo->getMTime()
        );
    }

    public function test_getCTime()
    {
        $this->assertEquals(
            \filectime(static::REMOTE_FILE),
            $this->sshFileInfo->getCTime()
        );
    }

    public function test_getType()
    {
        $this->assertEquals(
            \filetype(static::REMOTE_FILE),
            $this->sshFileInfo->getType()
        );
    }

    public function test_getPerms()
    {
        $this->assertEquals(
            '0644',
            $this->sshFileInfo->getPerms()
        );
    }

    public function test_getInode()
    {
        $this->assertEquals(
            \fileinode(static::REMOTE_FILE),
            $this->sshFileInfo->getInode()
        );
    }

    public function test_getSize()
    {
        $this->assertEquals(
            \filesize(static::REMOTE_FILE),
            $this->sshFileInfo->getSize()
        );
    }

    public function test_getOwner()
    {
        $this->assertEquals(
            \fileowner(static::REMOTE_FILE),
            $this->sshFileInfo->getOwner()
        );
    }

    public function test_getGroup()
    {
        $this->assertEquals(
            \filegroup(static::REMOTE_FILE),
            $this->sshFileInfo->getOwner()
        );
    }

    public function test_isWritable()
    {
        $this->assertEquals(
            \is_writable(static::REMOTE_FILE),
            $this->sshFileInfo->isWritable()
        );
    }

    public function test_isReadable()
    {
        $this->assertEquals(
            \is_readable(static::REMOTE_FILE),
            $this->sshFileInfo->isReadable()
        );
    }

    public function test_isExecutable()
    {
        $this->assertEquals(
            \is_executable(static::REMOTE_FILE),
            $this->sshFileInfo->isExecutable()
        );
    }

    public function test_isFile()
    {
        $this->assertEquals(
            \is_file(static::REMOTE_FILE),
            $this->sshFileInfo->isFile()
        );
    }

    public function test_isDir()
    {
        $this->assertEquals(
            \is_dir(static::REMOTE_FILE),
            $this->sshFileInfo->isDir()
        );
    }

    public function test_isLink_false()
    {
        $this->assertEquals(
            \is_link(static::REMOTE_FILE),
            $this->sshFileInfo->isLink()
        );
    }

    public function test_isLink_true()
    {
        $linkInfo = $this->factory->createSshFileInfo(
            static::REMOTE_FILE_LINK,
            $this->configurator
        );

        $this->assertEquals(
            \is_link(static::REMOTE_FILE_LINK),
            $linkInfo->isLink()
        );
    }

    public function test_getLinkTarget_without_link()
    {
        $this->assertEquals(
            static::REMOTE_FILE,
            $this->sshFileInfo->getLinkTarget()
        );
    }

    public function test_getLinkTarget_with_link()
    {
        $linkInfo = $this->factory->createSshFileInfo(
            static::REMOTE_FILE_LINK,
            $this->configurator
        );

        $this->assertEquals(
            static::REMOTE_FILE,
            $linkInfo->getLinkTarget()
        );
    }

    public function test_getRealPath()
    {
        $this->assertEquals(
            static::REMOTE_FILE,
            $this->sshFileInfo->getRealPath()
        );
    }

    public function test_getPathInfo()
    {
        $this->assertEquals(
            rtrim(static::REMOTE_PATH, '/'),
            $this->sshFileInfo->getPathInfo()->getPathname()
        );
    }

    public function test_getPathInfo_with_class()
    {
        $this->assertEquals(
            rtrim(static::REMOTE_PATH, '/'),
            $this->sshFileInfo->getPathInfo(VirtualSplFileInfo::class)->getPathname()
        );
    }

    public function test_getFileInfo()
    {
        $this->assertEquals(
            static::REMOTE_FILE,
            $this->sshFileInfo->getFileInfo()->getPathname()
        );

        $this->assertEquals(
            \pathinfo(static::REMOTE_FILE, \PATHINFO_EXTENSION),
            $this->sshFileInfo->getFileInfo()->getExtension()
        );

        $this->assertTrue(
            $this->sshFileInfo->getFileInfo()->isReadable()
        );
        $this->assertTrue(
            $this->sshFileInfo->getFileInfo()->isWritable()
        );
        $this->assertFalse(
            $this->sshFileInfo->getFileInfo()->isExecutable()
        );

        $this->assertTrue(
            $this->sshFileInfo->getFileInfo()->isFile()
        );
        $this->assertFalse(
            $this->sshFileInfo->getFileInfo()->isDir()
        );
        $this->assertFalse(
            $this->sshFileInfo->getFileInfo()->isLink()
        );

        $this->assertEquals(
            StatDefinesInterface::VALUE_FILE,
            $this->sshFileInfo->getFileInfo()->getType()
        );

        $this->assertEquals(
            basename(static::REMOTE_FILE, '.txt'),
            $this->sshFileInfo->getFileInfo()->getBasename('.txt')
        );

        $this->assertEquals(
            static::REMOTE_PATH,
            $this->sshFileInfo->getFileInfo()->getPath() . '/'
        );

        $this->assertEquals(
            \posix_getuid(),
            $this->sshFileInfo->getFileInfo()->getOwner()
        );

        $this->assertEquals(
            \posix_getgid(),
            $this->sshFileInfo->getFileInfo()->getGroup()
        );

        $info = new \SplFileInfo(static::REMOTE_FILE);
        $expected = decoct($info->getPerms() & 0777);
        $perms = decoct(octdec($this->sshFileInfo->getFileInfo()->getPerms()) & 0777);

        $this->assertEquals(
            $expected,
            $perms
        );
    }
}
