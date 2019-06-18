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

        $this->assertEquals($result['path'], static::REMOTE_NAME);
        $this->assertEquals($result['type'], 'file');
        $this->assertTrue(\is_resource($result['stream']));
    }

}
