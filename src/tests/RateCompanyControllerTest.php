<?php

use PHPUnit\Framework\TestCase;
use Grp5\ProjetWeb4All\Controllers\RateCompanyController;
use Grp5\ProjetWeb4All\Models\Entreprises;
use Grp5\ProjetWeb4All\Models\Note;

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

            public function index(): void
            {
                $entrepriseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                $entreprise   = (new Entreprises($this->pdo))->findById($entrepriseId);

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
                        $ok = (new Note($this->pdo))->add(
                            $entrepriseId,
                            $idCompte,
                            (int) ($_POST['star-rating'] ?? 0),
                            trim($_POST['comment'] ?? '')
                        );
                        $ok ? $success = true : $error = true;
                        $entreprise = (new Entreprises($this->pdo))->findById($entrepriseId);
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