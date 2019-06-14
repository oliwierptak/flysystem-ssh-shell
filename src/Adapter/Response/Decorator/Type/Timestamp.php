<?php

namespace League\Flysystem\SshShell\Adapter\Response\Decorator\Type;

use League\Flysystem\SshShell\Adapter\Response\Decorator\ResponseDecoratorInterface;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class Timestamp implements ResponseDecoratorInterface
{
    const TYPE = 'timestamp';

    public function decorate(VirtualSplFileInfo $metadata, array $response): array
    {
        $response[static::TYPE] = $metadata->getMTime();

        return $response;
    }

    public function getType(): string
    {
        return static::TYPE;
    }
}
