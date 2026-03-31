<?php

require_once __DIR__ . '/../Database.php';

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
        ORDER BY e.id_entreprise
    ');
}

$entreprises = $stmt->fetchAll(\PDO::FETCH_ASSOC);