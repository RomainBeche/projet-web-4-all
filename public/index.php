<?php
require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    // 'cache' => __DIR__ . '/../cache',
    'cache' => false // désactive le cache en dev
]);

$page = $_GET['page'] ?? 'accueil';
$allowedPages = [
    // 'accueil',
    'compte-entreprise',
    'compte-etudiant',
    'compte-pilote',
    'creation-compte-etudiant',
    'creation-compte-pilote',
    'creation-compte-validation',
    'creation-entreprise',
    'creer-offre',
    'modification-compte-entreprise',
    'modification-compte-etudiant',
    'modification-compte-pilote',
    'modification-compte-validation',
    'modification-offre-terminee',
    'modification-offre',
    'suppression-compte-1',
    'suppression-compte-2',
    'suppression-offre'
    ];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    $page = '404';
}

echo $twig->render("pages/{$page}.twig.html", [
    'currentPage' => $page,
]);