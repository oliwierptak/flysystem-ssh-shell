<?php

namespace League\Flysystem\SshShell\Adapter\Response\Decorator;

use Phuxtil\SplFileInfo\VirtualSplFileInfo;

interface ResponseDecoratorInterface
{
    public function decorate(VirtualSplFileInfo $metadata, array $response): array;

    public function getType(): string;
}
