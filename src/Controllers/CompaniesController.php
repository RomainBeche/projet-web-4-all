<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class CompaniesController extends Controller
{
    public function index(): void {
        require_once __DIR__ . '/../../src/Models/Entreprises.php';

        require_once __DIR__ . '/../../src/Pagination.php';
        require_once __DIR__ . '/../../src/PaginationEntreprises.php';

        $pdo = getConnection();
        $q_entreprises = trim($_GET['q'] ?? '');

        if ($q_entreprises !== '') {
            $stmt = $pdo->prepare(<<<SQL
                SELECT e.*
                FROM public.entreprise e
                WHERE
                    to_tsvector('french',
                        coalesce(e.nom, '')             || ' ' ||
                        coalesce(e.description, '')     || ' ' ||
                        coalesce(e.secteur, '')
                    ) @@ plainto_tsquery('french', :q)
                ORDER BY e.id_entreprise
            SQL);
            $stmt->execute([':q' => $q_entreprises]);
        } else {
            $stmt = $pdo->query('
               SELECT e.* 
               FROM public.entreprise e
               ORDER BY COALESCE(e."rating", -1) DESC
            ');
        }

        $entreprises = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pagination = new \PaginationEntreprises($entreprises, 8);
 
        $this->render('pages/entreprises.twig.html', [
            'currentPage' => 'entreprises',
            'entreprises'    => $pagination->getCurrentElements(),
            'navLinks'    => $pagination->getNavigationLinks('?page=entreprises&q=' . urlencode($q_entreprises) . '&'),
            'searchQuery' => $q_entreprises,
        ]);   
    }
}
