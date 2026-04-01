<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Annonces;

class HomeController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Database.php';

        $annonces = (new Annonces(getConnection()))->findAll('a.id_annonce DESC');

        $this->render('pages/accueil.twig.html', [
            'currentPage' => 'accueil',
            'annonces'    => array_slice($annonces, 0, 8),
        ]);
    }
}