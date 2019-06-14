<?php

namespace League\Flysystem\SshShell\Process;

use Symfony\Component\Process\Process;

class ProcessReader
{
    /**
     * @var \League\Flysystem\SshShell\Process\Ssh
     */
    protected $process;

    public function __construct(Ssh $process)
    {
        $this->process = $process;
    }

    public function find(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s find %s %s', [$prefix, $path, $postfix]);
    }

    public function read(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s cat %s %s', [$prefix, $path, $postfix]);
    }

    public function file(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s file -b %s %s', [$prefix, $path, $postfix]);
    }

    public function stat(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s stat %s %s', [$prefix, $path, $postfix]);
    }

    public function isWritable(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s [ -w %s ] && echo 0 %s', [$prefix, $path, $postfix]);
    }

    public function isReadable(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s [ -r %s ] && echo 0 %s', [$prefix, $path, $postfix]);
    }

    public function isExecutable(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s [ -x %s ] && echo 0 %s', [$prefix, $path, $postfix]);
    }

    public function getLinkTarget(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute('%s readlink -e %s', [$prefix, $path, $postfix]);
    }

    public function listContents(string $directory, bool $recursive = false)
    {
        $maxDepth = '';
        if (!$recursive) {
            $maxDepth = '-maxdepth 1';
        }

        return $this->process->execute(
            'find %s %s -print',
            [
                $directory,
                $maxDepth,
            ]
        );
    }
}
