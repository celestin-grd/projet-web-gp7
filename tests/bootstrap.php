<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// class FakeValidator
// {
//     public function validate($data, $rules)
//     {
//         return true; // 🔥 toujours valide
//     }

//     public function errors()
//     {
//         return [];
//     }
// }

// if (!class_exists('Validator')) {
//     class_alias(FakeValidator::class, 'Validator');
// }



require_once __DIR__ . '/../app/core/Autoloader.php';
Autoloader::register();

define('ENV', 'dev'); // dev | prod
define('APP_NAME', 'StageFinder');
define('BASE_URL', '');
define('CDN', 'https://web4all.local');
define('STATIQUE', 'https://static.web4all.local');
define('PREFIX', '');

define('ITEM_PER_PAGES', 3);
if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}
