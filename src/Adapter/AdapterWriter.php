<?php

namespace Phuxtil\Flysystem\SshShell\Adapter;

use Phuxtil\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use Phuxtil\Flysystem\SshShell\Process\ProcessWriter;

class AdapterWriter
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Process\ProcessWriter
     */
    protected $writer;

    /**
     * @var \Phuxtil\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter
     */
    protected $visibility;

    public function __construct(
        ProcessWriter $writer,
        VisibilityPermissionConverter $visibility
    ) {
        $this->writer = $writer;
        $this->visibility = $visibility;
    }

    public function write(string $path, string $contents): bool
    {
        $createPathProcess = $this->writer->mkdir(dirname($path));
        if (!$createPathProcess->isSuccessful()) {
            return false;
        }

        return $this->writeStringData($path, $contents);
    }

    /**
     * @param string $path
     * @param resource $resource
     *
     * @return bool
     */
    public function writeStream(string $path, $resource): bool
    {
        $createPathProcess = $this->writer->mkdir(dirname($path));
        if (!$createPathProcess->isSuccessful()) {
            return false;
        }

        return $this->writeStreamData($path, $resource);
    }

    public function update(string $path, string $contents): bool
    {
        return $this->writeStringData($path, $contents);
    }

    public function mkdir(string $path, string $visibility): bool
    {
        $perms = $this->visibility->toPermission($visibility, 'dir');
        if (!$perms) {
            return false;
        }

        $process = $this->writer->mkdir($path, $perms);

        return $process->isSuccessful();
    }

    public function setVisibility(string $path, string $visibility, string $type): bool
    {
        $perms = $this->visibility->toPermission($visibility, $type);
        if (trim($perms) === '') {
            return false;
        }

        $process = $this->writer->setVisibility($path, $perms);

        return $process->isSuccessful();
    }

    public function copy(string $source, string $destination): bool
    {
        $createPathProcess = $this->writer->mkdir(dirname($destination));
        if (!$createPathProcess->isSuccessful()) {
            return false;
        }

        $process = $this->writer->copy(
            $source,
            $destination
        );

        return $process->isSuccessful();
    }

    public function rename(string $path, string $newpath): bool
    {
        $createPathProcess = $this->writer->mkdir(dirname($newpath));
        if (!$createPathProcess->isSuccessful()) {
            return false;
        }

        $process = $this->writer->rename(
            $path,
            $newpath
        );

        return $process->isSuccessful();
    }

    public function delete(string $path): bool
    {
        $process = $this->writer->delete($path);

        return $process->isSuccessful();
    }

    public function rmdir(string $path): bool
    {
        $process = $this->writer->rmdir($path);

        return $process->isSuccessful();
    }

    /**
     * @param string $path
     * @param string $contents
     *
     * @return bool
     */
    protected function writeStringData(string $path, string $contents): bool
    {
        $resource = $this->createTempResource($contents);

        return $this->writeStreamData(
            $path,
            $resource
        );
    }

    /**
     * @param string $path
     * @param resource|false $resource
     *
     * @return bool
     */
    protected function writeStreamData(string $path, $resource): bool
    {
        if (!$resource) {
            return false;
        }

        $filename = \tempnam(\sys_get_temp_dir(), \time()) . '.tmp';
        $stream = fopen($filename, 'w+b');
        $result = $stream && stream_copy_to_stream($resource, $stream);

        if ($result === false || !fclose($stream)) {
            return false;
        }

        $process = $this->writer->write(
            $filename,
            $path
        );

        @unlink($filename);

        return $process->isSuccessful();
    }

    /**
     * @param string $contents
     *
     * @return resource|false
     */
    protected function createTempResource(string $contents)
    {
        $resource = tmpfile();
        $result = fwrite($resource, $contents);
        if ($result === false) {
            return false;
        }

        if (!rewind($resource)) {
            return false;
        }

        return $resource;
    }
}
