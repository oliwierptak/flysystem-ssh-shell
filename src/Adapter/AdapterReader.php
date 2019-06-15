<?php

namespace League\Flysystem\SshShell\Adapter;

use League\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo;
use League\Flysystem\SshShell\Process\ProcessReader;
use Phuxtil\Find\FindConfigurator;
use Phuxtil\Find\FindFacade;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use Phuxtil\Stat\StatFacade;

class AdapterReader
{
    /**
     * @var \League\Flysystem\SshShell\Process\ProcessReader
     */
    protected $reader;

    /**
     * @var \Phuxtil\Stat\StatFacade
     */
    protected $statFacade;

    /**
     * @var \League\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo
     */
    protected $statToSplFileInfo;

    public function __construct(
        ProcessReader $reader,
        StatFacade $statFacade,
        StatToSplFileInfo $statToSplFileInfo
    ) {
        $this->reader = $reader;
        $this->statFacade = $statFacade;
        $this->statToSplFileInfo = $statToSplFileInfo;
    }

    /**
     * @param string $path
     *
     * @return VirtualSplFileInfo
     */
    public function getMetadata(string $path): VirtualSplFileInfo
    {
        $process = $this->reader->stat($path);
        if (!$process->isSuccessful()) {
            return (new VirtualSplFileInfo($path))
                ->setWritable(false)
                ->setReadable(false)
                ->setExecutable(false)
                ->setFile(false)
                ->setDir(false)
                ->setLink(false);
        }

        $stat = $this->statFacade->process($process->getOutput());

        return $this->statToSplFileInfo->convert($stat);
    }

    public function read(string $path): string
    {
        $process = $this->reader->read($path);
        if (!$process->isSuccessful()) {
            return '';
        }

        return $process->getOutput();
    }

    public function listContents(string $directory, bool $recursive = false): array
    {
        $findFacade = new FindFacade();
        $configurator = new FindConfigurator();

        $process = $this->reader->listContents(
            $directory,
            $configurator->getFormat(),
            $configurator->getLineDelimiter(),
            $recursive
        );

        if (!$process->isSuccessful()) {
            return [];
        }

        $configurator->setFindOutput($process->getOutput());
        return $findFacade->process($configurator);
    }
}
