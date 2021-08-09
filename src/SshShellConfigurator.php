<?php

/**
 * Phuxtil\Flysystem\SshShell configuration file. Auto-generated.
 */

declare(strict_types=1);

namespace Phuxtil\Flysystem\SshShell;

use UnexpectedValueException;

class SshShellConfigurator
{
    protected const SHAPE_PROPERTIES = [
        'user' => 'null|string',
        'host' => 'null|string',
        'privateKey' => 'null|string',
        'authType' => 'null|string',
        'root' => 'null|string',
        'port' => 'null|int',
        'timeout' => 'null|int',
    ];

    protected const METADATA = [
        'user' => ['type' => 'string', 'default' => null],
        'host' => ['type' => 'string', 'default' => null],
        'privateKey' => ['type' => 'string', 'default' => null],
        'authType' => ['type' => 'string', 'default' => 'config'],
        'root' => ['type' => 'string', 'default' => '/tmp'],
        'port' => ['type' => 'int', 'default' => 22],
        'timeout' => ['type' => 'int', 'default' => 60],
    ];

    protected ?string $user = null;
    protected ?string $host = null;
    protected ?string $privateKey = null;
    protected ?string $authType = 'config';
    protected ?string $root = '/tmp';
    protected ?int $port = 22;

    /** SSH process timeout in seconds */
    protected ?int $timeout = 60;
    protected array $updateMap = [];

    public function setUser(?string $user): self
    {
        $this->user = $user; $this->updateMap['user'] = true; return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function requireUser(): string
    {
        $this->setupPopoProperty('user');

        if ($this->user === null) {
            throw new UnexpectedValueException('Required value of "user" has not been set');
        }
        return $this->user;
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host; $this->updateMap['host'] = true; return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function requireHost(): string
    {
        $this->setupPopoProperty('host');

        if ($this->host === null) {
            throw new UnexpectedValueException('Required value of "host" has not been set');
        }
        return $this->host;
    }

    public function hasHost(): bool
    {
        return $this->host !== null;
    }

    public function setPrivateKey(?string $privateKey): self
    {
        $this->privateKey = $privateKey; $this->updateMap['privateKey'] = true; return $this;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function requirePrivateKey(): string
    {
        $this->setupPopoProperty('privateKey');

        if ($this->privateKey === null) {
            throw new UnexpectedValueException('Required value of "privateKey" has not been set');
        }
        return $this->privateKey;
    }

    public function hasPrivateKey(): bool
    {
        return $this->privateKey !== null;
    }

    public function setAuthType(?string $authType): self
    {
        $this->authType = $authType; $this->updateMap['authType'] = true; return $this;
    }

    public function getAuthType(): ?string
    {
        return $this->authType;
    }

    public function requireAuthType(): string
    {
        $this->setupPopoProperty('authType');

        if ($this->authType === null) {
            throw new UnexpectedValueException('Required value of "authType" has not been set');
        }
        return $this->authType;
    }

    public function hasAuthType(): bool
    {
        return $this->authType !== null;
    }

    public function setRoot(?string $root): self
    {
        $this->root = $root; $this->updateMap['root'] = true; return $this;
    }

    public function getRoot(): ?string
    {
        return $this->root;
    }

    public function requireRoot(): string
    {
        $this->setupPopoProperty('root');

        if ($this->root === null) {
            throw new UnexpectedValueException('Required value of "root" has not been set');
        }
        return $this->root;
    }

    public function hasRoot(): bool
    {
        return $this->root !== null;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port; $this->updateMap['port'] = true; return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function requirePort(): int
    {
        $this->setupPopoProperty('port');

        if ($this->port === null) {
            throw new UnexpectedValueException('Required value of "port" has not been set');
        }
        return $this->port;
    }

    public function hasPort(): bool
    {
        return $this->port !== null;
    }

    /**
     * SSH process timeout in seconds
     */
    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout; $this->updateMap['timeout'] = true; return $this;
    }

    /**
     * SSH process timeout in seconds
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * SSH process timeout in seconds
     */
    public function requireTimeout(): int
    {
        $this->setupPopoProperty('timeout');

        if ($this->timeout === null) {
            throw new UnexpectedValueException('Required value of "timeout" has not been set');
        }
        return $this->timeout;
    }

    public function hasTimeout(): bool
    {
        return $this->timeout !== null;
    }

    #[\JetBrains\PhpStorm\ArrayShape(self::SHAPE_PROPERTIES)]
    public function toArray(): array
    {
        $data = [
            'user' => $this->user,
            'host' => $this->host,
            'privateKey' => $this->privateKey,
            'authType' => $this->authType,
            'root' => $this->root,
            'port' => $this->port,
            'timeout' => $this->timeout,
        ];

        array_walk(
            $data,
            function (&$value, $name) use ($data) {
                $popo = static::METADATA[$name]['default'];
                if (static::METADATA[$name]['type'] === 'popo') {
                    $value = $this->$name !== null ? $this->$name->toArray() : (new $popo)->toArray();
                }
            }
        );

        return $data;
    }

    public function fromArray(#[\JetBrains\PhpStorm\ArrayShape(self::SHAPE_PROPERTIES)] array $data): self
    {
        foreach (static::METADATA as $name => $meta) {
            $value = $data[$name] ?? $this->$name ?? null;
            $popoValue = $meta['default'];

            if ($popoValue !== null && $meta['type'] === 'popo') {
                $popo = new $popoValue;

                if (is_array($value)) {
                    $popo->fromArray($value);
                }

                $value = $popo;
            }

            $this->$name = $value;
            $this->updateMap[$name] = true;
        }

        return $this;
    }

    public function isNew(): bool
    {
        return empty($this->updateMap) === true;
    }

    public function listModifiedProperties(): array
    {
        return array_keys($this->updateMap);
    }

    public function requireAll(): self
    {
        $errors = [];

        try {
            $this->requireUser();
        }
        catch (\Throwable $throwable) {
            $errors['user'] = $throwable->getMessage();
        }
        try {
            $this->requireHost();
        }
        catch (\Throwable $throwable) {
            $errors['host'] = $throwable->getMessage();
        }
        try {
            $this->requirePrivateKey();
        }
        catch (\Throwable $throwable) {
            $errors['privateKey'] = $throwable->getMessage();
        }
        try {
            $this->requireAuthType();
        }
        catch (\Throwable $throwable) {
            $errors['authType'] = $throwable->getMessage();
        }
        try {
            $this->requireRoot();
        }
        catch (\Throwable $throwable) {
            $errors['root'] = $throwable->getMessage();
        }
        try {
            $this->requirePort();
        }
        catch (\Throwable $throwable) {
            $errors['port'] = $throwable->getMessage();
        }
        try {
            $this->requireTimeout();
        }
        catch (\Throwable $throwable) {
            $errors['timeout'] = $throwable->getMessage();
        }

        if (empty($errors) === false) {
            throw new UnexpectedValueException(
                implode("\n", $errors)
            );
        }

        return $this;
    }

    protected function setupPopoProperty($propertyName): void
    {
        if (static::METADATA[$propertyName]['type'] === 'popo' && $this->$propertyName === null) {
            $popo = static::METADATA[$propertyName]['default'];
            $this->$propertyName = new $popo;
        }
    }
}
