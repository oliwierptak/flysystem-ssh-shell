<?php

namespace League\Flysystem\SshShell\Adapter;

use League\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo;
use League\Flysystem\SshShell\Process\ProcessReader;
use Phuxtil\Find\FindConfigurator;
use Phuxtil\Find\FindFacadeInterface;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use Phuxtil\Stat\StatFacadeInterface;

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
     * @var FindFacadeInterface
     */
    protected $findFacade;

    /**
     * @var \League\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo
     */
    protected $statToSplFileInfo;

    public function __construct(
        ProcessReader $reader,
        StatFacadeInterface $statFacade,
        FindFacadeInterface $findFacade,
        StatToSplFileInfo $statToSplFileInfo
    ) {
        $this->reader = $reader;
        $this->statFacade = $statFacade;
        $this->findFacade = $findFacade;
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
            return (new VirtualSplFileInfo($path));
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

        $configurator->setFindOutput(trim($process->getOutput()));
        return $this->findFacade->process($configurator);
    }
}
