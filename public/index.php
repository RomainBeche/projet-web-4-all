<?php
ob_start();
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Grp5\ProjetWeb4All\Core\Router;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    // 'cache' => __DIR__ . '/../cache',
    'cache' => false // désactive le cache en dev
]);

// Load routes
$routes = require __DIR__ . '/../config/routes.php';

// Initialize Router
$router = new Router($routes);

// Get the requested page
$page = $_GET['page'] ?? 'accueil';

$router->route($page);
ob_end_flush();
