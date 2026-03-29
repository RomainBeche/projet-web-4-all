<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class CompanyDetailsController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Entreprises.php';     // TODO: REMOVE

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

        // Recherche de l'entreprise par ID
        $entreprise = null;
        foreach ($entreprises as $e) {
            if ($e['id'] === $id) {
                $entreprise = $e;
                break;
            }
        }

        // Entreprise introuvable → 404
        if ($entreprise === null) {
            $this->render('pages/404.twig.html');
            return;
        }

        $this->render('pages/detail-entreprise.twig.html', [
            'entreprise' => $entreprise,
        ]);
    }
}