# flysystem-ssh-shell

Flysystem adapter for SSH shell.
 
### Installation

```bash
composer require phuxtil/flysystem-ssh-shell 
```

### Requirements

The following programs installed and configured on local host:
- ssh
- scp

The following programs installed on the remote host:
- find
- cat
- stat
- rmdir
- mkdir
- chmod
- mv
- rm
- sh compatible shell

### Configuration

Use `League\Flysystem\SshShell\SshShellConfigurator` to pass options to adapter.

```php
$configurator = (new SshShellConfigurator())
    ->setRoot('/remote_server/path')
    ->setUser('remote_user')
    ->setHost('remote-ssh-host')
    ->setPrivateKey('path/to/id_rsa.private_key')
    ->setPort(22);
```

### Authentication

Two authentication methods are supported:

#### via ssh config

The value of `user@host` is configured in ssh config file.

```php
$configurator = (new SshShellConfigurator())
    ->setUser('user')
    ->setHost('host');
```
_Note: This is the default setting._

#### via ssh private key 

```php
$configurator = (new SshShellConfigurator())
    ->setUser('user')
    ->setHost('host')
    ->setPrivateKey('path/to/id_rsa.private_key');
```
Passed as `-i` option to ssh/scp.

_Note: To revert to default setting, unset private key value._



### Bootstrap

``` php
<?php

use League\Flysystem\Filesystem;
use League\Flysystem\SshShell\SshShellConfigurator;
use League\Flysystem\SshShell\SshShellFactory;

\error_reporting(\E_ALL);

include __DIR__ . '/vendor/autoload.php';

$configurator = (new SshShellConfigurator())
    ->setRoot('/tmp/remote_fs')
    ->setUser('user')
    ->setHost('host');

$adapter = (new SshShellFactory())->createAdapter($configurator);
$filesystem = new Filesystem($adapter);

```
