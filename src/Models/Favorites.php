<?php

require_once __DIR__ . '/../Database.php';

$pdo = getConnection();

// Liste des id_annonce en favori pour l'utilisateur connecté
$stmt = $pdo->prepare('
    SELECT id_annonce FROM public.favori WHERE id_compte = :id_compte
');
$stmt->execute([':id_compte' => $_SESSION['user_id']]);
$favoris = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_annonce');