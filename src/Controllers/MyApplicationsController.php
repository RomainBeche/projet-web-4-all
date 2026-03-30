<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class MyApplicationsController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Models/Annonces.php';     // TODO: REMOVE
        require_once __DIR__ . '/../../src/Models/Candidatures.php';     // TODO: REMOVE

        $this->requireLogin();

        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationAnnonces.php';
        
            $annoncesById = [];
        foreach ($annonces as $annonce) {
            $annoncesById[(int) $annonce['id']] = $annonce;
        }

        // Enrichit chaque candidature avec les données de son annonce
        $candidaturesEnrichies = [];
        foreach ($candidatures as $candidature) {
            $idOffre = (int) $candidature['id_offre'];
            $candidature['annonce'] = $annoncesById[$idOffre] ?? null;
            $candidaturesEnrichies[] = $candidature;
        }

        $this->render('pages/mes-candidatures.twig.html', [
            'currentPage'   => 'mes-candidatures',
            'candidatures'  => $candidaturesEnrichies,
        ]);
    }
}