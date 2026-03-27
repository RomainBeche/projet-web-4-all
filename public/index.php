<?php
require_once __DIR__ . '/../vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    // 'cache' => __DIR__ . '/../cache',
    'cache' => false // désactive le cache en dev
]);

$page = $_GET['page'] ?? 'accueil';
$allowedPages = [
    'accueil',
    'login',
    'logout',
    'deconnexion',
    'annonces',
    'compte-entreprise',
    'compte-etudiant',
    'compte-pilote',
    'creation-compte-etudiant',
    'creation-compte-pilote',
    'creation-compte-validation',
    'creation-entreprise',
    'creer-offre',
    'detail-annonce',
    'entreprises',
    'favoris',
    'modification-compte-entreprise',
    'modification-compte-etudiant',
    'modification-compte-pilote',
    'modification-compte-validation',
    'modification-offre-terminee',
    'modification-offre',
    'suppression-compte-1',
    'suppression-compte-2',
    'suppression-offre',
    'entreprises',
    'fiche-entreprise',
    'evaluation'
    ];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    $page = '404';
}

$pageFile = __DIR__ . "/../templates/pages/{$page}.php";

if (file_exists($pageFile)) {
    // Inclut le fichier PHP directement (il gère son propre echo/output)
    include $pageFile;
} else {
    // Fallback sur Twig si pas de .php
    echo $twig->render("pages/{$page}.twig.html", [
        'currentPage' => $page,
    ]);
}
