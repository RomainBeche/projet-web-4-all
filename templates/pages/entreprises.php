<?php
/* require_once __DIR__ . '/../../src/OffersDB.php';
require_once __DIR__ . '/../../src/pagination.php';

// $twig est hérité du scope de index.php via include — pas besoin de global
$pagination = new Pagination($offers, 8);

echo $twig->render('pages/entreprises.twig.html', [
    'currentPage' => 'entreprises',
    'offers'      => $pagination->getCurrentOffers(),
    'navLinks'    => $pagination->getNavigationLinks(),
]);
*/




try {
    echo $twig->render('pages/entreprises.twig.html', [
        'currentPage' => 'entreprises',
        'offers'      => [['Nom_entreprise' => 'Test', 'Secteur' => 'IT', 'Rating' => 4.0]],
        'navLinks'    => [],
    ]);
} catch (\Twig\Error\Error $e) {
    die("❌ Erreur Twig : " . $e->getMessage() . " (ligne " . $e->getTemplateLine() . ")");
}



