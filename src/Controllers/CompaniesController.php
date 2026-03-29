<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class CompaniesController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Entreprises.php';     // TODO: REMOVE
        
        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationEntreprises.php';

        $pagination = new \PaginationEntreprises($entreprises, 8);

        $this->render('pages/entreprises.twig.html', [
            'currentPage' => 'entreprises',
            'entreprises' => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=entreprises&')
        ]);
    }
}