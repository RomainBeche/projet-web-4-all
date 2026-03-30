<?php

require_once __DIR__ . '/../Database.php';

$pdo = getConnection();

$stmt = $pdo->query('
    SELECT a.*, e.nom AS entreprise_nom
    FROM public.annonce a
    LEFT JOIN public.entreprise e ON a.id_entreprise_appartient = e.id_entreprise
    ORDER BY a.id_annonce
');

$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Parse les tags jsonb --> tableau PHP
foreach ($annonces as &$annonce) {
    if (!empty($annonce['tags'])) {
        $annonce['tags'] = json_decode($annonce['tags'], true) ?? [];
    }
}
unset($annonce);