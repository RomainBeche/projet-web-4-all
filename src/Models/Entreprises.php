<?php

require_once __DIR__ . '/../Database.php';

$pdo = getConnection();

$stmt = $pdo->query("SELECT * FROM entreprise");

$entreprises = $stmt->fetch(\PDO::FETCH_ASSOC);



