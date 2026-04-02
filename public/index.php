<?php

date_default_timezone_set('Europe/Paris');
/**
 * Chargement du fichier .env situé à la racine du projet
 *
 * @param string $path nom du fichir à loader
 *
 * Charge dans la variable d'environnement $_ENV les données présente dans le fichier .env
 *
 * @return void
 */
function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

loadEnv(__DIR__ . '/../.env');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'web4all.local',    // ton domaine
    'secure' => true,              // mettre true -> HTTPS obligatoire
    'httponly' => true,             // impossible d’y accéder via JS
    'samesite' => 'Strict'          // ou Lax selon besoin
]);

require_once __DIR__ . '/../app/config/Constants.php';

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/Autoloader.php';
Autoloader::register();
View::init();

// Initialisation permissions guest si non définies
if (!isset($_SESSION['permissions'])) {
    $authModel = new AuthModel();
    $_SESSION['permissions'] = $authModel->getPermissions(GUEST);
}


$router = new Router();
$router->dispatch();
