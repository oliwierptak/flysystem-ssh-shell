<?php

namespace League\Flysystem\SshShell\FileInfo\Stat;

use Phuxtil\Chmod\ChmodFacade;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use Phuxtil\Stat\Output\Stat;

class StatToSplFileInfo
{
    /**
     * @var \Phuxtil\Chmod\ChmodFacade
     */
    protected $chmodFacade;

    public function __construct(ChmodFacade $chmodFacade)
    {
        $this->chmodFacade = $chmodFacade;
    }

    public function convert(Stat $stat): VirtualSplFileInfo
    {
        $isReadable = $this->chmodFacade->validateByOctal($stat->getPermission(), 'u', 'r') ||
            $this->chmodFacade->validateByOctal($stat->getPermission(), 'g', 'r') ||
            $this->chmodFacade->validateByOctal($stat->getPermission(), 'o', 'r');

        $isWritable = $this->chmodFacade->validateByOctal($stat->getPermission(), 'u', 'w') ||
            $this->chmodFacade->validateByOctal($stat->getPermission(), 'g', 'w') ||
            $this->chmodFacade->validateByOctal($stat->getPermission(), 'o', 'w');

        $isExecutable = $this->chmodFacade->validateByOctal($stat->getPermission(), 'u', 'x') ||
            $this->chmodFacade->validateByOctal($stat->getPermission(), 'g', 'x') ||
            $this->chmodFacade->validateByOctal($stat->getPermission(), 'o', 'x');

        $info = (new VirtualSplFileInfo($stat->getFilename()))
            ->setATime($stat->getDateAccess()->getTimestamp())
            ->setMTime($stat->getDateModify()->getTimestamp())
            ->setCTime($stat->getDateChange()->getTimestamp())
            ->setPerms($stat->getPermission())
            ->setOwner($stat->getUid())
            ->setGroup($stat->getGid())
            ->setType($stat->getType())
            ->setInode($stat->getInode())
            ->setSize($stat->getSize())
            ->setReadable($isReadable)
            ->setWritable($isWritable)
            ->setExecutable($isExecutable);

        $info->setFile($stat->isFile());
        $info->setDir($stat->isDir());
        $info->setLink($stat->isLink());

/*
        $process = $this->reader->isReadable($info->getPathname());
        $info->setReadable($process->isSuccessful());

        $process = $this->reader->isWritable($info->getPathname());
        $info->setWritable($process->isSuccessful());

        $process = $this->reader->isExecutable($info->getPathname());
        $info->setExecutable($process->isSuccessful());*/

        return $info;
    }

}
