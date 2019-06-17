<?php

namespace Phuxtil\Flysystem\SshShell\Adapter;

use Phuxtil\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo;
use Phuxtil\Flysystem\SshShell\Process\ProcessReader;
use Phuxtil\Find\FindConfigurator;
use Phuxtil\Find\FindFacadeInterface;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;
use Phuxtil\Stat\StatFacadeInterface;

class AdapterReader
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Process\ProcessReader
     */
    protected $reader;

    /**
     * @var \Phuxtil\Stat\StatFacadeInterface
     */
    protected $statFacade;

    /**
     * @var FindFacadeInterface
     */
    protected $findFacade;

    /**
     * @var \Phuxtil\Flysystem\SshShell\FileInfo\Stat\StatToSplFileInfo
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

    /**
     * @param string $path
     *
     * @return resource|false
     */
    public function readStream(string $path)
    {
        $contents = $this->read($path);
        if ($contents === '') {
            return false;
        }

        $resource = tmpfile();
        fwrite($resource, $contents);

        return $resource;
    }

    /**
     * @param string $directory
     * @param bool $recursive
     *
     * @return \Phuxtil\SplFileInfo\VirtualSplFileInfo[]
     */
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
