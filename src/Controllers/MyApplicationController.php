<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Candidatures;

class MyApplicationController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        require_once __DIR__ . '/../../src/Database.php';

        $candidatureId = (int) ($_GET['id'] ?? 0);
        if ($candidatureId === 0) {
            header('Location: /?page=accueil');
            exit;
        }

        $candidature = (new Candidatures(getConnection()))
            ->findByIdAndCompte($candidatureId, $_SESSION['user_id']);

        if (!$candidature) {
            header('Location: /?page=accueil');
            exit;
        }

        $this->render('pages/ma-candidature.twig.html', [
            'candidature' => $candidature,
            'annonce'     => [
                'titre'      => $candidature['titre'],
                'entreprise' => $candidature['entreprise'],
            ],
        ]);
    }
}