# flysystem-ssh-shell

Flysystem adapter for SSH shell.
 
### Installation

```bash
composer require phuxtil/flysystem-ssh-shell 
```

_Note_: Use v1.x for compatibility with PHP v7.0.x
_Note_: Use v2.x for compatibility with PHP v7.2+

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

Use `\Phuxtil\Flysystem\SshShell\SshShellConfigurator` to pass options to adapter.

```php
$configurator = (new \Phuxtil\Flysystem\SshShell\SshShellConfigurator())
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
$configurator = (new \Phuxtil\Flysystem\SshShell\SshShellConfigurator())
    ->setUser('user')
    ->setHost('host');
```
_Note: This is the default setting._

#### via ssh private key 

```php
$configurator = (new \Phuxtil\Flysystem\SshShell\SshShellConfigurator())
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
use Phuxtil\Flysystem\SshShell\SshShellConfigurator;
use Phuxtil\Flysystem\SshShell\SshShellFactory;

\error_reporting(\E_ALL);

include __DIR__ . '/vendor/autoload.php';

$configurator = (new SshShellConfigurator())
    ->setRoot('/tmp/remote_fs')
    ->setUser('user')
    ->setHost('host');

$adapter = (new SshShellFactory())->createAdapter($configurator);

$filesystem = new Filesystem($adapter);

```


### TDD

Default root directory on remote host is `/tmp/remote_fs`.

Available parameters:
- `TESTS_SSH_USER` 
- `TESTS_SSH_HOST`
- `TEST_SSH_PORT` (optional, default 22)

Run tests with:

```shell
TESTS_SSH_USER=... TESTS_SSH_HOST=... vendor/bin/phpunit --group acceptance
``` 
