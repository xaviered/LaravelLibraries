<?php

/**
 * Usage: Change to Laravel project directory. Run
 * php /full/path/to/Data/DataStore/init.php
 */

namespace ixavier\LaravelLibraries\Data\DataStore;

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(getcwd().'/.env');

$database = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];

echo <<<OEL
CREATE DATABASE IF NOT EXISTS {$database};

CREATE USER '$user'@'localhost' IDENTIFIED BY '{$pass}';
GRANT ALL PRIVILEGES ON {$database}.* TO 'ixavier'@'localhost';

CREATE USER '$user'@'%' IDENTIFIED BY '{$pass}';
GRANT ALL PRIVILEGES ON {$database}.* TO 'ixavier'@'%';

OEL;
