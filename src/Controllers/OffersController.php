<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class OffersController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Annonces.php';     // TODO: REMOVE

        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationAnnonces.php';

        $pagination = new \PaginationAnnonces($annonces, 8);

        $this->render('pages/annonces.twig.html', [
            'currentPage' => 'annonces',
            'annonces' => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=annonces&')
        ]);
    }
}