<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Process;

use Symfony\Component\Process\Process;

class ProcessReader
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Process\Ssh
     */
    protected $process;

    public function __construct(Ssh $process)
    {
        $this->process = $process;
    }

    public function read(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute(
            '%s %s cat %s %s',
            [
                $prefix,
                sprintf('[[ -f \'%s\' ]] && ', $path),
                $path,
                $postfix,
            ]
        );
    }

    public function stat(string $path, string $prefix = '', string $postfix = ''): Process
    {
        return $this->process->execute(
            '%s %s stat %s %s',
            [
                $prefix,
                sprintf('[[ -f \'%s\' ]] && ', $path),
                $path,
                $postfix,
            ]
        );
    }

    /**
     * Note: symbolic links will be resolved.
     *
     * @param string $directory
     * @param string $format
     * @param string $lineDelimiter
     * @param bool $recursive
     *
     * @return \Symfony\Component\Process\Process
     */
    public function listContents(string $directory, string $format, string $lineDelimiter, bool $recursive = false)
    {
        $maxDepth = '';
        if (!$recursive) {
            $maxDepth = '-maxdepth 1';
        }

        return $this->process->execute(
            '[[ -d \'%s\' ]] && echo \$(find -L %s %s -printf "\""%s%s"\"")',
            [
                \escapeshellarg($directory),
                \escapeshellarg($directory),
                $maxDepth,
                $format,
                $lineDelimiter,
            ]
        );
    }
}
