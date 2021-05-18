<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Acceptance\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

/**
 * @group flysystem-ssh-shell
 * @group acceptance
 * @group adapter
 * @group writer
 */
class MountManagerTest extends AbstractTestCase
{
    /**
     * @var \League\Flysystem\MountManager
     */
    protected $mountManager;

    protected function setUp(): void
    {
        parent::setUp();

        $sshAdapter = $this->factory->createAdapter(
            $this->configurator
        );

        $localAdapter = new Local(
            static::REMOTE_PATH
        );

        $this->mountManager = new MountManager(
            [
                'ssh' => new Filesystem($sshAdapter),
                'local' => new Filesystem($localAdapter),
            ]
        );
    }

    public function test_has()
    {
        $this->setupRemoteFile();

        $hasLocal = $this->mountManager->has('local://' . static::REMOTE_NAME);
        $hasSsh = $this->mountManager->has('ssh://' . static::REMOTE_NAME);

        $this->assertTrue($hasLocal);
        $this->assertTrue($hasSsh);
    }

    /**
     * @return void
     * @throws \League\Flysystem\FileExistsException
     */
    public function test_copy()
    {
        $this->setupRemoteFile();

        $this->mountManager->copy(
            'ssh://' . static::REMOTE_NAME,
            'local://' . static::REMOTE_NEWPATH_NAME
        );

        $this->assertContent();
    }

    public function test_move()
    {
        $this->setupRemoteFile();

        $this->mountManager->move(
            'ssh://' . static::REMOTE_NAME,
            'local://' . static::REMOTE_NEWPATH_NAME
        );

        $this->assertContent();
        $this->assertFileDoesNotExist(static::REMOTE_FILE);
    }
}
