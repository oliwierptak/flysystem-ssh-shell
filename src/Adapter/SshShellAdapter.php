<?php

namespace League\Flysystem\SshShell\Adapter;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\CanOverwriteFiles;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use League\Flysystem\Util;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class SshShellAdapter extends AbstractAdapter implements CanOverwriteFiles, AdapterInterface
{
    /**
     * @var \League\Flysystem\SshShell\Adapter\AdapterReader
     */
    protected $reader;

    /**
     * @var \League\Flysystem\SshShell\Adapter\AdapterWriter
     */
    protected $writer;

    /**
     * @var \League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter
     */
    protected $visibility;

    public function __construct(
        AdapterReader $reader,
        AdapterWriter $writer,
        VisibilityPermissionConverter $visibility
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->visibility = $visibility;
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
        $result = $this->writer->write($location, $contents);
        if (!$result) {
            return false;
        }

        $metadata = $this->updatePathVisibility($path, $config);
        if (!$metadata) {
            $metadata = $this->getMetadata($path);
        }

        return $metadata;
    }

    /**
     * Not supported, use write() instead.
     *
     * {@inheritDoc}
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     * @see \League\Flysystem\SshShell\Adapter\SshShellAdapter::write()
     *
     */
    public function writeStream($path, $resource, Config $config)
    {
        return false;
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
        $metadata = $this->reader->getMetadata($location);
        if (!$metadata->isReadable() && $metadata->isVirtual()) {
            return false;
        }

        $result = $this->writer->update($location, $contents);
        if (!$result) {
            return false;
        }

        return $this->prepareMetadataResult($metadata);
    }

    /**
     * Not supported, use update() instead.
     *
     * {@inheritDoc}
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     * @see \League\Flysystem\SshShell\Adapter\SshShellAdapter::update()
     *
     */
    public function updateStream($path, $resource, Config $config)
    {
        return false;
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

        return $this->getMetadata($dirname);
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
        $metadata = $this->reader->getMetadata($location);

        if (!$this->writer->setVisibility($location, $visibility, $metadata->getType())) {
            return false;
        }

        $perms = $this->visibility->toPermission($visibility, $metadata->getType());
        $metadata->setPerms($perms);

        return $this->prepareMetadataResult($metadata);
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
        $metadata = $this->reader->getMetadata($location);

        $result['contents'] = $this->reader->read($path);

        return array_merge($result, $metadata->toArray());
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
        $directory = $this->applyPathPrefix($directory);

        return $this->reader->listContents($directory, $recursive);
    }

    /**
     * Not supported, use read() instead.
     *
     * {@inheritDoc}
     *
     * @param string $path
     *
     * @return array|false
     * @see \League\Flysystem\SshShell\Adapter\SshShellAdapter::read()
     *
     */
    public function readStream($path)
    {
        return false;
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
        return $this->getMetadata($path);
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
        $result['visibility'] = $this->visibility->toVisibility($metadata->getPerms(), $metadata->getType());
        $result['timestamp'] = $metadata->getMTime();
        $result['mimetype'] = Util::guessMimeType($metadata->getPathname(), '');

        return array_merge($result, $metadata->toArray());
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
}
