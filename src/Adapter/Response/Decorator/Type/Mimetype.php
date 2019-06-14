<?php

namespace League\Flysystem\SshShell\Adapter\Response\Decorator\Type;

use League\Flysystem\SshShell\Adapter\Response\Decorator\ResponseDecoratorInterface;
use League\Flysystem\Util\MimeType as FlysystemMimeType;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class Mimetype implements ResponseDecoratorInterface
{
    const TYPE = 'mimetype';

    public function decorate(VirtualSplFileInfo $metadata, array $response): array
    {
        if (!$metadata->isDir()) {
            $response[static::TYPE] = FlysystemMimeType::detectByFilename($metadata->getPathname());
        }

        return $response;
    }

    public function getType(): string
    {
        return static::TYPE;
    }
}
