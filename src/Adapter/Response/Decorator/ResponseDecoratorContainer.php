<?php

namespace League\Flysystem\SshShell\Adapter\Response\Decorator;

use League\Flysystem\SshShell\Adapter\Response\Decorator\Type\Mimetype;
use League\Flysystem\SshShell\Adapter\Response\Decorator\Type\Timestamp;
use League\Flysystem\SshShell\Adapter\Response\Decorator\Type\Visibility;
use League\Flysystem\SshShell\Adapter\VisibilityPermission\VisibilityPermissionConverter;

class ResponseDecoratorContainer
{
    protected $classes = [
        Visibility::class,
        Timestamp::class,
        Mimetype::class
    ];

    /**
     * @var VisibilityPermissionConverter
     */
    protected $visibilityPermission;

    public function __construct(VisibilityPermissionConverter $visibilityPermission)
    {
        $this->visibilityPermission = $visibilityPermission;
    }

    /**
     * @return \League\Flysystem\SshShell\Adapter\Response\Decorator\ResponseDecoratorInterface[]
     */
    public function collect(): array
    {
        $decoratorCollection = [
            $this->createVisibility(),
            $this->createTimestamp(),
            $this->createMimetype()
        ];

        $result = [];
        foreach ($decoratorCollection as $decorator) {
            $result[$decorator->getType()] = $decorator;
        }

        return $result;
    }

    private function createVisibility(): Visibility
    {
        return new Visibility(
            $this->visibilityPermission
        );
    }

    private function createTimestamp(): Timestamp
    {
        return new Timestamp();
    }

    private function createMimetype(): Mimetype
    {
        return new Mimetype();
    }
}
