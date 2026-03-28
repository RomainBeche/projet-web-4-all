<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class FavoritesController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Annonces.php';

        $this->render('pages/favoris.twig.html', [
            'annonces' => $annonces,
        ]);
    }
}