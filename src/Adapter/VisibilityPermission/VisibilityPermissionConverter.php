<?php

namespace League\Flysystem\SshShell\Adapter\VisibilityPermission;

class VisibilityPermissionConverter
{
    /**
     * @var array
     */
    protected $permissionMap = [
        'file' => [
            'public' => '0644',
            'private' => '0600',
        ],
        'dir' => [
            'public' => '0755',
            'private' => '0700',
        ],
    ];

    public function toPermission(string $visibility, string $type): string
    {
        if (!isset($this->permissionMap[$type][$visibility])) {
            return '';
        }

        return $this->permissionMap[$type][$visibility];
    }

    public function toVisibility(string $permission, string $type): string
    {
        if (!isset($this->permissionMap[$type])) {
            return '';
        }

        return array_search($permission, $this->permissionMap[$type]);
    }
}

