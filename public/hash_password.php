<?php
$dotenv = parse_ini_file(__DIR__ . '/../.env');

$pdo = new PDO(
    "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
    $dotenv['DB_USER'],
    $dotenv['DB_PASSWORD']
);

$comptes = $pdo->query("SELECT id_compte, mot_de_passe FROM compte")->fetchAll(PDO::FETCH_ASSOC);

foreach ($comptes as $compte) {
    // Ignore les mots de passe déjà hashés
    if (str_starts_with($compte['mot_de_passe'], '$2y$')) {
        echo "id {$compte['id_compte']} : déjà hashé, ignoré. <br>";
        continue;
    }

    $hash = password_hash($compte['mot_de_passe'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE compte SET mot_de_passe = :hash WHERE id_compte = :id");
    $stmt->execute([':hash' => $hash, ':id' => $compte['id_compte']]);
    echo "id {$compte['id_compte']} : mot de passe hashé ✅<br>";
}
?>