<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Entreprises;

class CompaniesController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Database.php';
        require_once __DIR__ . '/../../src/paginationEntreprises.php';

        $pdo   = getConnection();
        $model = new Entreprises($pdo);
        $q     = trim($_GET['q'] ?? '');

        $entreprises = $q !== '' ? $model->search($q) : $model->findAll();

        $pagination = new \PaginationEntreprises($entreprises, 8);

        $this->render('pages/entreprises.twig.html', [
            'currentPage' => 'entreprises',
            'entreprises' => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=entreprises&q=' . urlencode($q) . '&'),
            'searchQuery' => $q,
        ]);
    }
}