<?php
$dotenv = parse_ini_file(__DIR__ . '/.env');

$pdo = new PDO(
    "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
    $dotenv['DB_USER'],
    $dotenv['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Créer la table entreprise
$pdo->exec("
    CREATE TABLE IF NOT EXISTS entreprise (
        id SERIAL PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        ville VARCHAR(100),
        secteur VARCHAR(100),
        created_at TIMESTAMP DEFAULT NOW()
    )
");
echo "Table créée\n";

// Insérer des exemples
$pdo->exec("
    INSERT INTO entreprise (nom, ville, secteur) VALUES
    ('Google', 'Paris', 'Tech'),
    ('Airbus', 'Toulouse', 'Aéronautique'),
    ('Carrefour', 'Massy', 'Distribution')
");
echo "Données insérées\n";

// Récupérer tous les éléments
$stmt = $pdo->query("SELECT * FROM entreprise");
$entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entreprises as $e) {
    echo "- {$e['id']} | {$e['nom']} | {$e['ville']} | {$e['secteur']}\n";
}
