<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class VisibilityTest extends AbstractTestCase
{
    public function test_setVisibility_to_private()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_NAME, AdapterInterface::VISIBILITY_PRIVATE);

        $expected = [
            'path' => static::REMOTE_NAME,
            'visibility' => AdapterInterface::VISIBILITY_PRIVATE
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_setVisibility_to_public()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_NAME, AdapterInterface::VISIBILITY_PUBLIC);

        $expected = [
            'path' => static::REMOTE_NAME,
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_setVisibility_invalid()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_NAME, 'invalid');

        $this->assertFalse($result);
    }

    public function test_setVisibility_invalid_path()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->setVisibility(static::REMOTE_INVALID_NAME, AdapterInterface::VISIBILITY_PUBLIC);

        $this->assertFalse($result);
    }
}
