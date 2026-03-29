<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class FavoritesController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Annonces.php';     // TODO: REMOVE

        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationAnnonces.php';

        $favoris = $_SESSION['favoris'] ?? [];

        // On filtre les annonces dont l'id est dans les favoris
        $annoncesFavoris = array_filter($annonces, function($a) use ($favoris) {
            return in_array($a['id'], $favoris);
        });

        $pagination = new \PaginationAnnonces(array_values($annoncesFavoris), 8);

        $this->render('pages/favoris.twig.html', [
            'currentPage' => 'annonces',
            'annonces' => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=annonces&')
        ]);
    }
}