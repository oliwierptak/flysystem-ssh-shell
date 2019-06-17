<?php

namespace Phuxtil\Flysystem\SshShell\Process;

use Symfony\Component\Process\Process;

class ProcessWriter
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Process\Scp
     */
    protected $scp;

    /**
     * @var \Phuxtil\Flysystem\SshShell\Process\Ssh
     */
    protected $ssh;

    public function __construct(Scp $scp, Ssh $ssh)
    {
        $this->scp = $scp;
        $this->ssh = $ssh;
    }

    public function write(string $source, string $destination): Process
    {
        $command = sprintf(
            '%s %s',
            $this->formatPath($source, false),
            $this->formatPath($destination, true)
        );

        return $this->scp->execute($command, []);
    }

    public function mkdir(string $path, string $mode = '0755'): Process
    {
        return $this->ssh->execute(
            'mkdir -p %s -m %s',
            [
                \escapeshellcmd($path),
                \escapeshellcmd($mode),
            ]
        );
    }

    public function rmdir(string $path): Process
    {
        return $this->ssh->execute(
            '[ -d %s ] && rmdir %s',
            [
                \escapeshellcmd($path),
                \escapeshellcmd($path),
            ]
        );
    }

    public function setVisibility(string $path, string $mode): Process
    {
        return $this->ssh->execute(
            'chmod %s %s',
            [
                \escapeshellcmd($mode),
                \escapeshellcmd($path),
            ]
        );
    }

    public function copy(string $source, string $destination): Process
    {
        $command = sprintf(
            '%s %s',
            $this->formatPath($source, false),
            $this->formatPath($destination, true)
        );

        return $this->scp->execute($command, []);
    }

    public function rename(string $source, string $destination): Process
    {
        $destination = \escapeshellcmd($destination);

        return $this->ssh->execute(
            'mv %s %s',
            [
                \escapeshellcmd($source),
                \escapeshellcmd($destination),
            ]
        );
    }

    public function delete(string $path): Process
    {
        $path = \escapeshellcmd($path);

        return $this->ssh->execute(
            '[ -f %s ] && rm %s',
            [
                $path,
                $path,
            ]
        );
    }

    protected function formatPath(string $path, bool $prefixUserAndHost): string
    {
        $pattern = '<USER_AT_HOST>:%s';
        if (!$prefixUserAndHost) {
            $pattern = '%s';
        }

        $path = \escapeshellcmd($path);

        return sprintf($pattern, $path);
    }
}
