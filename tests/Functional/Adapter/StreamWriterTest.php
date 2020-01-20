<?php

declare(strict_types = 1);

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class StreamWriterTest extends AbstractTestCase
{
    public function test_writeStream_should_set_visibility()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();
        $config->set('visibility', AdapterInterface::VISIBILITY_PRIVATE);

        $expected = [
            'type' => 'file',
            'size' => \filesize(static::LOCAL_FILE),
            'path' => static::REMOTE_NEWPATH_NAME,
            'visibility' => AdapterInterface::VISIBILITY_PRIVATE,
        ];

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertEquals($expected, $result);
        $this->assertContent();
    }

    public function test_writeStream_should_create_path()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $expected = [
            'type' => 'file',
            'size' => \filesize(static::LOCAL_FILE),
            'path' => static::REMOTE_NEWPATH_NAME
        ];

        $this->assertEquals($expected, $result);
        $this->assertContent();
    }

    public function test_writeStream_should_return_false_when_invalid_resource()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $config = new Config();
        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, false, $config);

        $this->assertFalse($result);
    }

    public function test_writeStream_should_return_false_when_ssh_command_fails()
    {
        $this->configurator->setPort(0);
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertFalse($result);
    }

    public function test_updateStream()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();

        $result = $adapter->updateStream(static::REMOTE_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $expected = [
            'type' => 'file',
            'size' => \filesize(static::LOCAL_FILE),
            'path' => static::REMOTE_NAME
        ];

        $this->assertEquals($expected, $result);
        $this->assertContent(static::REMOTE_FILE);
    }
}
