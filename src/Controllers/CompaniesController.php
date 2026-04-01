<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class CompaniesController extends Controller
{
    public function index(): void {
        require_once __DIR__ . '/../../src/Models/Entreprises.php';

        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/paginationEntreprises.php';

        $pagination = new \PaginationEntreprises($entreprises, 8);
 
        $this->render('pages/entreprises.twig.html', [
            'currentPage' => 'entreprises',
            'entreprises'    => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=entreprises&q=' . urlencode($q_entreprises) . '&'),
            'searchQuery' => $q_entreprises,
        ]);   
    }
}