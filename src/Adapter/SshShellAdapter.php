<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Adapter;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\CanOverwriteFiles;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Phuxtil\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use League\Flysystem\Util;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class SshShellAdapter extends AbstractAdapter implements CanOverwriteFiles, AdapterInterface
{
    /**
     * @var \Phuxtil\Flysystem\SshShell\Adapter\AdapterReader
     */
    protected $reader;

    /**
     * @var \Phuxtil\Flysystem\SshShell\Adapter\AdapterWriter
     */
    protected $writer;

    /**
     * @var \Phuxtil\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter
     */
    protected $visibilityConverter;

    public function __construct(
        AdapterReader $reader,
        AdapterWriter $writer,
        VisibilityPermissionConverter $visibilityConverter
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->visibilityConverter = $visibilityConverter;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $size = $this->writer->write($location, $contents);
        if ($size === false) {
            return false;
        }

        $visibility = $this->updatePathVisibility($path, $config);

        $result = [
            'contents' => $contents,
            'type' => 'file',
            'size' => $size,
            'path' => $path,
        ];

        if ($visibility === false) {
            return $result;
        }

        return array_merge($result, $visibility);
    }

    /**
     * @param string $path
     * @param resource|bool $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $size = $this->writer->writeStream($location, $resource);
        if ($size === false) {
            return false;
        }

        $visibility = $this->updatePathVisibility($path, $config);

        $result = [
            'type' => 'file',
            'size' => $size,
            'path' => $path,
        ];

        if ($visibility === false) {
            return $result;
        }

        return array_merge($result, $visibility);
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        $location = $this->applyPathPrefix($path);
        $size = $this->writer->update($location, $contents);
        if ($size === false) {
            return false;
        }

        $visibility = $this->updatePathVisibility($path, $config);

        $result = [
            'contents' => $contents,
            'type' => 'file',
            'size' => $size,
            'path' => $path,
        ];

        if ($visibility === false) {
            return $result;
        }

        return array_merge($result, $visibility);
    }

    /**
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $locationPath = $this->applyPathPrefix($path);
        $locationNewPath = $this->applyPathPrefix($newpath);

        return $this->writer->rename($locationPath, $locationNewPath);
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $locationPath = $this->applyPathPrefix($path);
        $locationNewPath = $this->applyPathPrefix($newpath);

        return $this->writer->copy($locationPath, $locationNewPath);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);

        return $this->writer->delete($location);
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        $location = $this->applyPathPrefix($dirname);

        return $this->writer->rmdir($location);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $location = $this->applyPathPrefix($dirname);
        $visibility = $config->get('visibility', AdapterInterface::VISIBILITY_PUBLIC);

        if (!$this->writer->mkdir($location, $visibility)) {
            return false;
        }

        return [
            'path' => $dirname,
            'type' => 'dir',
        ];
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        $location = $this->applyPathPrefix($path);
        if (!$this->writer->setVisibility($location, $visibility, 'file')) {
            return false;
        }

        return [
            'path' => $path,
            'visibility' => $visibility,
        ];
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $location = $this->applyPathPrefix($path);
        $metadata = $this->reader->getMetadata($location);

        return $metadata->isReadable() && !$metadata->isVirtual();
    }

    /**
     * ReadResource a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $location = $this->applyPathPrefix($path);
        $contents = $this->reader->read($location);

        if ($contents === false) {
            return false;
        }

        return [
            'type' => 'file',
            'path' => $path,
            'contents' => $contents,
        ];
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        $path = $this->applyPathPrefix($directory);
        $contents = $this->reader->listContents($path, $recursive);

        $result = [];
        foreach ($contents as $fileInfo) {
            $result[] = $this->fileInfoToFilesystemResult($fileInfo);
        }

        return $result;
    }

    /**
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $location = $this->applyPathPrefix($path);
        $stream = $this->reader->readStream($location);

        if ($stream === false) {
            return false;
        }

        return [
            'type' => 'file',
            'path' => $path,
            'stream' => $stream,
        ];
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        $location = $this->applyPathPrefix($path);
        $metadata = $this->reader->getMetadata($location);
        if ($metadata->isVirtual()) {
            return false;
        }

        return $this->prepareMetadataResult($metadata);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $location = $this->applyPathPrefix($path);

        return [
            'path' => $path,
            'type' => 'file',
            'mimetype' => Util::guessMimeType($location, ''),
        ];
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        return $this->getMetadata($path);
    }

    protected function prepareMetadataResult(VirtualSplFileInfo $metadata): array
    {
        $result['visibility'] = $this->visibilityConverter->toVisibility((string)$metadata->getPerms(), $metadata->getType());
        $result['timestamp'] = $metadata->getMTime();
        $result['mimetype'] = Util::guessMimeType($metadata->getPathname(), '');

        return array_merge($this->fileInfoToFilesystemResult($metadata), $result);
    }

    /**
     * @param string $path
     * @param \League\Flysystem\Config $config
     *
     * @return array|false
     */
    protected function updatePathVisibility(string $path, Config $config)
    {
        $visibility = $config->get('visibility');
        if ($visibility) {
            return $this->setVisibility($path, $visibility);
        }

        return false;
    }

    public function removePathPrefix($path)
    {
        $path = trim((string)parent::removePathPrefix($path));

        if ($path === '') {
            $path = '/';
        }

        return $path;
    }

    /**
     * @param \Phuxtil\SplFileInfo\VirtualSplFileInfo $fileInfo
     *
     * @return array
     */
    protected function fileInfoToFilesystemResult(VirtualSplFileInfo $fileInfo): array
    {
        $item = $fileInfo->toArray();

        $item['path'] = $this->removePathPrefix($fileInfo->getPathname());
        $item['basename'] = $item['path'];
        $item['dirname'] = $this->removePathPrefix($fileInfo->getPath());
        $item['filename'] = pathinfo($fileInfo->getPathname(), \PATHINFO_FILENAME);
        $item['timestamp'] = $fileInfo->getMTime();
        unset($item['pathname']);

        return $item;
    }
}
