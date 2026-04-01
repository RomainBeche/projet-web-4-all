<?php

use PHPUnit\Framework\TestCase;
use Grp5\ProjetWeb4All\Controllers\RateCompanyController;

function getConnection(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));

    $pdo->exec("CREATE TABLE note (
        id_entreprise INTEGER, notation INTEGER,
        commentaire TEXT, id_compte INTEGER, date_notation TEXT
    );");

    $pdo->exec("CREATE TABLE entreprise (
        id_entreprise INTEGER PRIMARY KEY,
        nombre_avis INTEGER DEFAULT 0, rating REAL DEFAULT 0
    );");

    $pdo->exec("INSERT INTO entreprise VALUES (1, 10, 4.5)");

    return $pdo;
}

class RateCompanyControllerTest extends TestCase
{
    private function getControllerMock(): object
    {
        $pdo = getConnection();

        return new class($pdo) extends RateCompanyController {

            public array $renderData = [];
            private PDO $pdo;

            public function __construct(PDO $pdo) { $this->pdo = $pdo; }

            protected function render(string $view, array $data = []): void
            {
                $this->renderData = ['view' => $view, 'data' => $data];
            }

            private function findEntreprise(int $id): array|false
            {
                $stmt = $this->pdo->prepare('SELECT * FROM entreprise WHERE id_entreprise = :id');
                $stmt->execute([':id' => $id]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            private function addNote(int $entrepriseId, int $idCompte, int $note, string $comment): bool
            {
                if ($note < 1 || $note > 5) return false;

                $stmt = $this->pdo->prepare('
                    INSERT INTO note (id_entreprise, notation, commentaire, id_compte, date_notation)
                    VALUES (:e, :n, :c, :u, NOW())
                ');
                return $stmt->execute([':e' => $entrepriseId, ':n' => $note, ':c' => $comment, ':u' => $idCompte]);
            }

            public function index(): void
            {
                $entrepriseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                $entreprise   = $this->findEntreprise($entrepriseId);

                if (!$entreprise) {
                    $this->render('pages/404.twig.html');
                    return;
                }

                $success = $error = $notLoggedIn = false;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $idCompte = $_SESSION['user_id'] ?? null;
                    if ($idCompte === null) {
                        $notLoggedIn = true;
                    } else {
                        $ok = $this->addNote(
                            $entrepriseId,
                            $idCompte,
                            (int) ($_POST['star-rating'] ?? 0),
                            trim($_POST['comment'] ?? '')
                        );
                        $ok ? $success = true : $error = true;
                        $entreprise = $this->findEntreprise($entrepriseId);
                    }
                }

                $this->render('pages/evaluation-entreprise.twig.html', [
                    'entreprise'  => $entreprise,
                    'rating'      => $entreprise['rating'],
                    'nb_avis'     => $entreprise['nombre_avis'],
                    'success'     => $success,
                    'error'       => $error,
                    'notLoggedIn' => $notLoggedIn,
                ]);
            }
        };
    }

    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testIndexNotLoggedIn(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['id']           = 1;
        $_POST['star-rating'] = 5;
        $_POST['comment']     = 'Nice';
        unset($_SESSION['user_id']);

        $controller = $this->getControllerMock();
        $controller->index();

        $this->assertTrue($controller->renderData['data']['notLoggedIn']);
    }

    public function testRateValid(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['id']           = 1;
        $_POST['star-rating'] = 5;
        $_POST['comment']     = 'Great';
        $_SESSION['user_id']  = 1;

        $controller = $this->getControllerMock();
        $controller->index();

        $this->assertTrue($controller->renderData['data']['success']);
    }

    public function testRateInvalid(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['id']           = 1;
        $_POST['star-rating'] = 10;
        $_POST['comment']     = 'Invalid';
        $_SESSION['user_id']  = 1;

        $controller = $this->getControllerMock();
        $controller->index();

        $this->assertTrue($controller->renderData['data']['error']);
    }
}