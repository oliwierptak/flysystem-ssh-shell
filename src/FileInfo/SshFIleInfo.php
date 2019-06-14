<?php

namespace League\Flysystem\SshShell\FileInfo;

use League\Flysystem\SshShell\Adapter\AdapterReader;
use League\Flysystem\SshShell\Process\ProcessReader;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class SshFileInfo extends VirtualSplFileInfo
{
    /**
     * @var \League\Flysystem\SshShell\Adapter\AdapterReader
     */
    protected $adapterReader;

    /**
     * @var \League\Flysystem\SshShell\Process\ProcessReader
     */
    protected $processReader;

    /**
     * @var VirtualSplFileInfo
     */
    protected $metadata;

    public function __construct(
        $file_name,
        AdapterReader $adapterReader,
        ProcessReader $processReader
    ) {
        parent::__construct($file_name);

        $this->adapterReader = $adapterReader;
        $this->processReader = $processReader;
    }

    protected function getMetadata(): VirtualSplFileInfo
    {
        if ($this->metadata === null) {
            $this->metadata = $this->adapterReader->getMetadata($this->getPathname());
        }

        return $this->metadata;
    }

    public function getPerms()
    {
        if ($this->perms === -1) {
            $this->perms = $this->getMetadata()->getPerms();
        }

        return $this->perms;
    }

    public function getInode()
    {
        if ($this->inode === -1) {
            $this->inode = $this->getMetadata()->getInode();
        }

        return $this->inode;
    }

    public function getSize()
    {
        if ($this->size === -1) {
            $this->size = $this->getMetadata()->getSize();
        }

        return $this->size;
    }

    public function getOwner()
    {
        if ($this->owner === -1) {
            $this->owner = $this->getMetadata()->getOwner();
        }

        return $this->owner;
    }

    public function getGroup()
    {
        if ($this->group === -1) {
            $this->group = $this->getMetadata()->getGroup();
        }

        return $this->group;
    }

    public function getATime()
    {
        if ($this->aTime === -1) {
            $this->aTime = $this->getMetadata()->getATime();
        }

        return $this->aTime;
    }

    public function getMTime()
    {
        if ($this->mTime === -1) {
            $this->mTime = $this->getMetadata()->getMTime();
        }

        return $this->mTime;
    }

    public function getCTime()
    {
        if ($this->cTime === -1) {
            $this->cTime = $this->getMetadata()->getCTime();
        }

        return $this->cTime;
    }

    public function getType()
    {
        if ($this->isVirtual()) {
            $this->type = $this->getMetadata()->getType();
        }

        return $this->type;
    }

    public function isWritable()
    {
        if ($this->writable === -1) {
            $this->writable = $this->getMetadata()->isWritable();
        }

        return $this->writable;
    }

    public function isReadable()
    {
        if ($this->readable === -1) {
            $this->readable = $this->getMetadata()->isReadable();
        }

        return $this->readable;
    }

    public function isExecutable()
    {
        if ($this->executable === -1) {
            $this->executable = $this->getMetadata()->isExecutable();
        }

        return $this->executable;
    }

    public function isFile()
    {
        if ($this->file === -1) {
            $this->file = $this->getMetadata()->isFile();
        }

        return $this->file;
    }

    public function isDir()
    {
        if ($this->dir === -1) {
            $this->dir = $this->getMetadata()->isDir();
        }

        return $this->dir;
    }

    public function isLink()
    {
        if ($this->link === -1) {
            $this->link = $this->getMetadata()->isLink();
        }

        return $this->link;
    }

    public function getLinkTarget()
    {
        if ($this->linkTarget === -1) {
            if (!$this->isLink()) {
                $this->linkTarget = $this->getPathname();
            }
            else {
                $process = $this->processReader->getLinkTarget($this->getPathname());
                $this->linkTarget = trim($process->getOutput());
            }
        }

        return $this->linkTarget;
    }

    public function getFileInfo($class_name = null)
    {
        if ($class_name === null) {
            return clone $this;
        }

        return parent::getFileInfo($class_name);
    }

    public function getPathInfo($class_name = null)
    {
        if ($class_name === null) {
            $info = new static(
                $this->getPath(),
                $this->adapterReader,
                $this->processReader
            );

            return $info;
        }

        return parent::getPathInfo($class_name);
    }
}
