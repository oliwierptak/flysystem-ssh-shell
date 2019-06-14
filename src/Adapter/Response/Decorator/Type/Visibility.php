<?php

namespace League\Flysystem\SshShell\Adapter\Response\Decorator\Type;

use League\Flysystem\SshShell\Adapter\Response\Decorator\ResponseDecoratorInterface;
use League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;
use Phuxtil\SplFileInfo\VirtualSplFileInfo;

class Visibility implements ResponseDecoratorInterface
{
    const TYPE = 'visibility';

    /**
     * @var \League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter
     */
    protected $visibilityPermission;

    public function __construct(VisibilityPermissionConverter $visibilityPermission)
    {
        $this->visibilityPermission = $visibilityPermission;
    }

    public function decorate(VirtualSplFileInfo $metadata, array $response): array
    {
        $visibility = $this->visibilityPermission->toVisibility($metadata->getPerms(), $metadata->getType());

        $response[static::TYPE] = $visibility;

        return $response;
    }

    public function getType(): string
    {
        return static::TYPE;
    }
}
