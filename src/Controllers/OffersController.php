<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\Candidatures;

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
            'candidatures' => "(SELECT COUNT(*) FROM candidature
                                WHERE candidature.id_annonce = a.id_annonce) $dir",
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

    public function mesAnnonces(): void
    {
        require_once __DIR__ . '/../../src/Database.php';

        $pdo = getConnection();
        $idCompte = $_SESSION['user_id'] ?? null;

        if (!$idCompte) {
            $this->render('pages/404.twig.html');
            return;
        }

        $stmt = $pdo->prepare("
            SELECT a.*, e.nom AS entreprise_nom
            FROM public.annonce a
            LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
            WHERE a.id_compte = :id
        ");
        $stmt->execute([':id' => $idCompte]);
        $annonces = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($annonces as &$annonce) {
            $annonce['tags'] = json_decode($annonce['tags'] ?? '[]', true);
            $annonce['type'] = strtolower($annonce['type'] ?? '');
        }
        unset($annonce);

        $this->render('pages/mes-annonces.twig.html', [
            'currentPage' => 'mes-annonces',
            'annonces'    => $annonces,
        ]);
    }
        

    public function create(): void
    {
        $this->requireLogin();

        $error = null;
        $succes = null;

        require_once __DIR__ . '/../../src/Database.php';
        $pdo = getConnection();

        $stmtEntreprises = $pdo->query("SELECT id_entreprise, nom FROM entreprise ORDER BY nom ASC");
        $entreprises = $stmtEntreprises->fetchAll(\PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre        = trim($_POST['titre'] ?? '');
            $description  = trim($_POST['description'] ?? '');
            $remuneration = trim($_POST['remuneration'] ?? '');
            $date         = trim($_POST['date'] ?? '');
            $idEntreprise = trim($_POST['entreprise'] ?? '');
            $secteur      = trim($_POST['secteur'] ?? '');
            $type         = trim($_POST['type'] ?? '');
            $lieu         = trim($_POST['lieu'] ?? '');
            $duree        = trim($_POST['duree'] ?? '');
            $niveau       = trim($_POST['niveau'] ?? '');
            // Récupération et nettoyage des tags
            $tagsRaw = $_POST['tags'] ?? [];
            $tags = array_values(
                array_filter(
                    array_map('trim', $tagsRaw),
                    fn($t) => $t !== ''   // ignore les cases vides
                )
            );
            $tagsJson = json_encode($tags, JSON_UNESCAPED_UNICODE);


            if (
                empty($titre) || empty($description) || empty($remuneration) ||
                empty($date) || empty($idEntreprise) || empty($type) ||
                empty($lieu) || empty($duree) || empty($niveau) || empty($tags)
            ) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                $idCompte = $_SESSION['user_id'] ?? null;

                if (!$idCompte) {
                    $this->render('pages/404.twig.html');
                    return;
                }

                $id_annonce = $pdo->query('SELECT COALESCE(MAX(id_annonce), 0) + 1 FROM annonce')->fetchColumn();

                $stmt = $pdo->prepare("
                    INSERT INTO annonce (
                        id_annonce, titre, description, base_remuneration, date,
                        id_entreprise_appartient, type, lieu, duree, vues,
                        niveau, secteur, tags, id_compte
                    )
                    VALUES (
                        :id_annonce, :titre, :description, :base_remuneration, :date,
                        :id_entreprise_appartient, :type, :lieu, :duree, :vues,
                        :niveau, :secteur, :tags, :id_compte
                    )
                ");

                $stmt->execute([
                    ':id_annonce'               => $id_annonce,
                    ':titre'                    => $titre,
                    ':description'              => $description,
                    ':base_remuneration'        => $remuneration,
                    ':date'                     => $date,
                    ':id_entreprise_appartient' => $idEntreprise,
                    ':type'                     => $type,
                    ':lieu'                     => $lieu,
                    ':duree'                    => $duree,
                    ':vues'                     => 0,
                    ':niveau'                   => $niveau,
                    ':secteur'                  => $secteur,
                    ':tags'                     => $tagsJson,
                    ':id_compte'                => $idCompte,
                ]);

                $succes = "L'annonce \"$titre\" a été créée avec succès.";
            }
        }

        $this->render('pages/creer-offre.twig.html', [
            'currentPage' => 'creer-offre',
            'error'       => $error,
            'succes'      => $succes,
            'entreprise'  => $entreprises,
        ]);
    }


}