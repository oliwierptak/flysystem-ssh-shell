<?php

\error_reporting(\E_ALL);

\define('TESTS_DIR', \getcwd() . \DIRECTORY_SEPARATOR . 'tests' . \DIRECTORY_SEPARATOR);
\define('TESTS_FIXTURE_DIR', \TESTS_DIR . 'fixtures' . \DIRECTORY_SEPARATOR);
\define('TESTS_SSH_USER', getenv('TESTS_SSH_USER', true));
\define('TESTS_SSH_HOST', getenv('TESTS_SSH_HOST', true));

$key = getenv('TESTS_SSH_KEY', true);
if ($key === false) {
    $key = '';
}
\define('TESTS_SSH_KEY', $key);

$port = getenv('TESTS_SSH_PORT', true);
if ($port === false) {
    $port = 22;
}
\define('TESTS_SSH_PORT', $port);

if (trim(TESTS_SSH_USER) === '') {
    throw new InvalidArgumentException('Environmental value of TESTS_SSH_USER is not set');
}

if (trim(TESTS_SSH_HOST) === '') {
    throw new InvalidArgumentException('Environmental value of TESTS_SSH_HOST is not set');
}

