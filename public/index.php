<?php

use CodeIgniter\Boot;
use Config\Paths;

$minPHPVersion = '8.5.12';
if (version_compare(PHP_VERSION, $minPHPVersion, '<')) {
    http_response_code(503);
    exit("Your PHP version must be {$minPHPVersion} or higher. Current version: " . PHP_VERSION);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';
$paths = new Paths();

require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
