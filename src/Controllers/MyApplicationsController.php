<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Candidatures;

class MyApplicationsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        require_once __DIR__ . '/../../src/Database.php';

        $model = new Candidatures(getConnection());
        $raw   = $model->findByCompte($_SESSION['user_id']);

        // Reconstruit la clé 'annonce' attendue par la vue Twig
        $candidatures = array_map(function ($c) {
            $c['annonce'] = [
                'id_annonce'    => $c['id_annonce'],
                'titre'         => $c['titre'],
                'lieu'          => $c['lieu'],
                'type'          => $c['type'],
                'duree'         => $c['annonce_duree'],
                'entreprise_nom' => $c['entreprise_nom'],
            ];
            return $c;
        }, $raw);

        $this->render('pages/mes-candidatures.twig.html', [
            'currentPage'  => 'mes-candidatures',
            'candidatures' => $candidatures,
        ]);
    }
}