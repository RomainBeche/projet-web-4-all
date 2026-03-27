<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Grp5\ProjetWeb4All\Core\Router;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);

// Load routes
$routes = require __DIR__ . '/../config/routes.php';

// Initialize Router
$router = new Router($routes);

// Get the requested page
$page = $_GET['page'] ?? 'accueil';

$router->route($page);
