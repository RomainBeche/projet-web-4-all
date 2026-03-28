<?php
ob_start();
session_start();




// Simulation temporaire (à supprimer quand la BDD sera prêt)
// CHARLES : LIGNES A SUPPRIMER SI ON TEST LE LOGIN FFS.
/*
$_SESSION['user_id']     = 1;
$_SESSION['user_nom']    = 'DIEMUNSH';
$_SESSION['user_prenom'] = 'Nicolas';
$_SESSION['user_email'] = 'nicolas.diemunsch@viacesi.fr';
$_SESSION['user_role']   = 'etudiant'; // 'etudiant', 'pilote'
*/




require_once __DIR__ . '/../vendor/autoload.php';

use Grp5\ProjetWeb4All\Core\Router;

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
