<?php

ob_start();

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Load .env
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// App settings
define("nastavak", $_ENV['APP_PATH']);
date_default_timezone_set($_ENV['APP_TIMEZONE']);

$site     = $_SERVER['DOCUMENT_ROOT'] . nastavak;
$home_url = $_ENV['APP_URL'];

$super_admin = [1, 104, 132];

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$records_per_page = 9999;
$from_record_num = ($records_per_page * $page) - $records_per_page;
