<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class OffersController extends Controller
{
    public function index(): void
    {
        require_once __DIR__ . '/../../src/Database.php';
        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationAnnonces.php';

        $pdo        = getConnection();
        $q_annonces = trim($_GET['q'] ?? '');
        $sort       = $_GET['sort'] ?? '';
        $dir        = strtoupper($_GET['dir'] ?? '') === 'ASC' ? 'ASC' : 'DESC';

        $sortLabels = [
            'duree'        => 'Trier par durée',
            'likes'        => 'Annonces les plus likées',
            'candidatures' => 'Plus de candidatures',
        ];

        $orderBy = match($sort) {
            'duree'        => "CAST(REGEXP_REPLACE(a.duree, '[^0-9]', '', 'g') AS INTEGER) $dir",
            'likes'        => "a.vues $dir",
            'candidatures' => "nb_candidatures $dir",
            default        => 'a.id_annonce ASC',
        };

        if ($q_annonces !== '') {
            $stmt = $pdo->prepare(<<<SQL
                SELECT a.*, e.nom AS entreprise_nom
                FROM public.annonce a
                LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
                WHERE
                    to_tsvector('french',
                        coalesce(a.titre, '')        || ' ' ||
                        coalesce(a.description, '')  || ' ' ||
                        coalesce(a.lieu, '')         || ' ' ||
                        coalesce(a.secteur, '')      || ' ' ||
                        coalesce(a.type::text, '')   || ' ' ||
                        coalesce(a.niveau, '')       || ' ' ||
                        coalesce(e.nom, '')
                    ) @@ to_tsquery('french', :q)
                ORDER BY $orderBy
            SQL);
            $stmt->execute([':q' => $q_annonces . ':*']);
        } else {
            $stmt = $pdo->query("
                SELECT a.*, e.nom AS entreprise_nom
                FROM public.annonce a
                LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
                ORDER BY $orderBy
            ");
        }

        $annonces = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($annonces as &$annonce) {
            $annonce['tags'] = json_decode($annonce['tags'] ?? '[]', true);
            $annonce['type'] = strtolower($annonce['type'] ?? '');
        }
        unset($annonce);

        $pagination = new \PaginationAnnonces($annonces, 8);

        $this->render('pages/annonces.twig.html', [
            'currentPage' => 'annonces',
            'annonces'    => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks(
                '?page=annonces&q=' . urlencode($q_annonces) . '&sort=' . urlencode($sort) . '&dir=' . $dir . '&'
            ),
            'searchQuery' => $q_annonces,
            'sort'        => $sort,
            'sortLabel'   => $sortLabels[$sort] ?? null,
            'dir'         => $dir,
        ]);
    }
}