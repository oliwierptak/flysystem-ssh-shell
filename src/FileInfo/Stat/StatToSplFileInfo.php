<?php

namespace Phuxtil\Flysystem\SshShell\FileInfo\Stat;

use Phuxtil\Chmod\ChmodFacadeInterface;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use Phuxtil\Stat\Output\Stat;

class StatToSplFileInfo
{
    /**
     * @var \Phuxtil\Chmod\ChmodFacadeInterface
     */
    protected $chmodFacade;

    public function __construct(ChmodFacadeInterface $chmodFacade)
    {
        $this->chmodFacade = $chmodFacade;
    }

    public function convert(Stat $stat): VirtualSplFileInfo
    {
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
            ->setReadable($this->chmodFacade->isReadable($stat->getPermission()))
            ->setWritable($this->chmodFacade->isWritable($stat->getPermission()))
            ->setExecutable($this->chmodFacade->isExecutable($stat->getPermission()));

        $info->setFile($stat->isFile());
        $info->setDir($stat->isDir());
        $info->setLink($stat->isLink());

        return $info;
    }

}
