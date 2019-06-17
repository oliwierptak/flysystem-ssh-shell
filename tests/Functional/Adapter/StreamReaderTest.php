<?php

namespace TestsPhuxtilFlysystemSshShell\Functional\Adapter;

use TestsPhuxtilFlysystemSshShell\Helper\AbstractTestCase;

class StreamReaderTest extends AbstractTestCase
{
    public function test_readStream()
    {
        $this->setupRemoteFile();

        $adapter = $this->factory->createAdapter(
            $this->configurator
        );

        $result = $adapter->readStream(static::REMOTE_NAME);

        $this->assertPathInfo($result, static::REMOTE_PATH, static::REMOTE_FILE);
        $this->assertResult($result);
        $this->assertContent(static::REMOTE_FILE);

        $this->assertTrue(\is_resource($result['stream']));

        //return ['type' => 'file', 'path' => $path, 'stream' => $stream];
    }

}
