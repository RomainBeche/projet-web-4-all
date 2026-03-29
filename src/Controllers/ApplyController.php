<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class ApplyController extends Controller
{
    public function index(): void
    {
        $annonceId = isset($_GET['id']) ? (int) $_GET['id'] : null;

        if ($annonceId === null) {
            header('Location: ?page=annonces');
            exit;
        }

        require_once __DIR__ . '/../../src/Models/Annonces.php';

        // Recherche de l'annonce correspondante
        $annonce = null;
        foreach ($annonces as $a) {
            if ((int) $a['id'] === $annonceId) {
                $annonce = $a;
                break;
            }
        }

        // L'id ne correspond à aucune annonce connue
        if ($annonce === null) {
            header('Location: ?page=annonces');
            exit;
        }

        $this->render('pages/postuler.twig.html', [
            'currentPage' => 'postuler',
            'annonce'       => $annonce,
        ]);
    }
}