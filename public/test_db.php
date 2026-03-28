<?php
$dotenv = parse_ini_file(__DIR__ . '/../.env');
try {
    $pdo = new PDO(
        "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']};connect_timeout=5",
        $dotenv['DB_USER'],
        $dotenv['DB_PASSWORD']
    );
    echo "Connexion réussie !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

// Créer la table entreprise
$pdo->exec("
    SELECT * FROM compte;
");
echo "Tous les comptes\n";


// Récupérer tous les éléments
$stmt = $pdo->query("SELECT * FROM entreprise");
$entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entreprises as $e) {
    echo "- {$e['id']} | {$e['nom']} | {$e['ville']} | {$e['secteur']}\n";
}
