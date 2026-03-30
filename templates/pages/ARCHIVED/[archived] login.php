<?php
// templates/pages/login.php
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: ?page=accueil');
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

// Connexion BDD
$dotenv = parse_ini_file(__DIR__ . '/../../.env');
try {
    $pdo = new PDO(
        "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
        $dotenv['DB_USER'],
        $dotenv['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Initialisation Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

$error = null;
$success = null;
$email = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Compte WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id']    = $user['id_compte'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];
            header('Location: ?page=accueil');
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}

echo $twig->render('pages/login.twig.html', [
    'error'       => $error,
    'success'     => $success,
    'email'       => $email,
    'currentPage' => 'login',
]);
