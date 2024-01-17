<?php

declare(strict_types = 1);

namespace Phuxtil\Flysystem\SshShell\Adapter;

use League\Flysystem\PathPrefixer;
use League\Flysystem\Config;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Phuxtil\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class SshShellAdapter
{
    protected PathPrefixer $pathPrefix;

    protected FinfoMimeTypeDetector $mimeTypeDetector;

    public function __construct(
        protected AdapterReader $reader,
        protected AdapterWriter $writer,
        protected VisibilityPermissionConverter $visibilityConverter,

    ) {
        $this->pathPrefix = new PathPrefixer('/');
        $this->mimeTypeDetector = new FinfoMimeTypeDetector();
    }

    public function setPathPrefix(string $prefix): void
    {
        $this->pathPrefix->stripDirectoryPrefix($prefix);
    }

    public function write(string $path, string $contents, Config $config): array|false
    {
        $location = $this->pathPrefix->prefixPath($path);
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
    public function writeStream(string $path, $resource, Config $config): array|false
    {
        $location = $this->pathPrefix->prefixPath($path);
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

    public function update(string $path, string $contents, Config $config): array|false
    {
        $location = $this->pathPrefix->prefixPath($path);
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
    public function updateStream(string $path, $resource, Config $config): array|false
    {
        return $this->writeStream($path, $resource, $config);
    }

    public function rename(string $path, string $newPath): bool
    {
        $locationPath = $this->pathPrefix->prefixPath($path);
        $locationNewPath = $this->pathPrefix->prefixPath($newPath);

        return $this->writer->rename($locationPath, $locationNewPath);
    }

    public function copy(string $path, string $newPath): bool
    {
        $locationPath = $this->pathPrefix->prefixPath($path);
        $locationNewPath = $this->pathPrefix->prefixPath($newPath);

        return $this->writer->copy($locationPath, $locationNewPath);
    }

    public function delete(string $path): bool
    {
        $location = $this->pathPrefix->prefixPath($path);

        return $this->writer->delete($location);
    }

    public function deleteDir(string $dirname): bool
    {
        $location = $this->pathPrefix->prefixPath($dirname);

        return $this->writer->rmdir($location);
    }

    public function createDir(string $dirname, Config $config): array|false
    {
        $location = $this->pathPrefix->prefixPath($dirname);
        $visibility = $config->get('visibility', Visibility::PUBLIC);

        if (!$this->writer->mkdir($location, $visibility)) {
            return false;
        }

        return [
            'path' => $dirname,
            'type' => 'dir',
        ];
    }

    public function setVisibility(string $path, string $visibility): array|false
    {
        $location = $this->pathPrefix->prefixPath($path);
        if (!$this->writer->setVisibility($location, $visibility, 'file')) {
            return false;
        }

        return [
            'path' => $path,
            'visibility' => $visibility,
        ];
    }

    public function has(string $path): array|bool|null
    {
        $location = $this->pathPrefix->prefixPath($path);
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
    public function read(string $path): array|false
    {
        $location = $this->pathPrefix->prefixPath($path);
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

    public function listContents(string $directory = '', bool $recursive = false): array
    {
        $path = $this->pathPrefix->prefixPath($directory);
        $contents = $this->reader->listContents($path, $recursive);

        $result = [];
        foreach ($contents as $fileInfo) {
            $result[] = $this->fileInfoToFilesystemResult($fileInfo);
        }

        return $result;
    }

    public function readStream(string $path): false|array
    {
        $location = $this->pathPrefix->prefixPath($path);
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

    public function getMetadata(string $path): false|array
    {
        $location = $this->pathPrefix->prefixPath($path);
        $metadata = $this->reader->getMetadata($location);
        if ($metadata->isVirtual()) {
            return false;
        }

        return $this->prepareMetadataResult($metadata);
    }

    public function getSize(string $path): false|array
    {
        return $this->getMetadata($path);
    }

    public function getMimetype(string $path): false|array
    {
        $location = $this->pathPrefix->prefixPath($path);

        return [
            'path' => $path,
            'type' => 'file',
            'mimetype' => $this->mimeTypeDetector->detectMimeType($location, ''),
        ];
    }

    public function getTimestamp(string $path): false|array
    {
        return $this->getMetadata($path);
    }

    public function getVisibility(string $path): false|array
    {
        return $this->getMetadata($path);
    }

    protected function prepareMetadataResult(VirtualSplFileInfo $metadata): array
    {
        $result['visibility'] = $this->visibilityConverter->toVisibility((string)$metadata->getPerms(), $metadata->getType());
        $result['timestamp'] = $metadata->getMTime();
        $result['mimetype'] = $this->mimeTypeDetector->detectMimeType($metadata->getPathname(), '');

        return array_merge($this->fileInfoToFilesystemResult($metadata), $result);
    }

    protected function updatePathVisibility(string $path, Config $config): false|array
    {
        $visibility = $config->get('visibility');
        if ($visibility) {
            return $this->setVisibility($path, $visibility);
        }

        return false;
    }

    public function removePathPrefix(string $path): string
    {
        $path = trim($this->pathPrefix->stripPrefix($path));

        if ($path === '') {
            $path = '/';
        }

        return $path;
    }

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
