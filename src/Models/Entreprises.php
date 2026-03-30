<?php

require_once __DIR__ . '/../Database.php';

$pdo = getConnection();

$stmt = $pdo->query('
    SELECT e.* 
    FROM public.entreprise e
    ORDER BY e."rating" DESC
');

$entreprises = $stmt->fetchALL(PDO::FETCH_ASSOC);