<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Annonces.php';     // TODO: REMOVE

        $this->render('pages/accueil.twig.html', [
            'annonces' => $annonces,
        ]);
    }
}