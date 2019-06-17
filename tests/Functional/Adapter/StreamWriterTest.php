<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class StreamWriterTest extends AbstractTestCase
{
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

        $this->assertPathInfo($result);
        $this->assertResult($result);
        $this->assertContent();
    }

    public function test_writeStream_should_set_visibility()
    {
        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $stream = fopen(static::LOCAL_FILE, 'r+');
        $config = new Config();
        $config->set('visibility', AdapterInterface::VISIBILITY_PRIVATE);

        $result = $adapter->writeStream(static::REMOTE_NEWPATH_NAME, $stream, $config);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->assertPathInfo($result);
        $this->assertResult($result, AdapterInterface::VISIBILITY_PRIVATE, '0600');
        $this->assertContent();
    }

    public function test_writeStream_should_return_false()
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
}
