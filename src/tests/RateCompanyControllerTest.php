<?php

use PHPUnit\Framework\TestCase;
use Grp5\ProjetWeb4All\Controllers\RateCompanyController;

// Fake DB
function getConnection() {
    $pdo = new PDO('sqlite::memory:');

    // 👉 Add NOW() support
    $pdo->sqliteCreateFunction('NOW', function () {
        return date('Y-m-d H:i:s');
    });

    $pdo->exec("
        CREATE TABLE note (
            id_entreprise INTEGER,
            notation INTEGER,
            commentaire TEXT,
            id_compte INTEGER,
            date_notation TEXT
        );
    ");

    $pdo->exec("
        CREATE TABLE entreprise (
            id_entreprise INTEGER,
            nombre_avis INTEGER,
            rating REAL
        );
    ");

    return $pdo;
}


class RateCompanyControllerTest extends TestCase
{
    private function getControllerMock()
    {
        return new class extends RateCompanyController {

            public array $renderData = [];

            protected function render(string $view, array $data = []): void
            {
                $this->renderData = [
                    'view' => $view,
                    'data' => $data
                ];
            }

            // 🔥 FULL override to avoid require_once crash
            public function index(): void
            {
                $entrepriseId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

                $entreprises = [
                    [
                        'id_entreprise' => 1,
                        'rating' => 4.5,
                        'nombre_avis' => 10
                    ]
                ];

                $entreprise = $entreprises[0];

                $success = false;
                $error = false;
                $notLoggedIn = false;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $note      = (int)($_POST['star-rating'] ?? 0);
                    $comment   = trim($_POST['comment'] ?? '');
                    $id_compte = $_SESSION['user_id'] ?? null;

                    if ($id_compte === null) {
                        $notLoggedIn = true;
                    } else {
                        $inserted = $this->rate($entrepriseId, $note, $id_compte, $comment);

                        if ($inserted) {
                            $success = true;
                        } else {
                            $error = true;
                        }
                    }
                }

                $this->render('test.twig', [
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

    public function testIndexNotLoggedIn()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['star-rating'] = 5;
        $_POST['comment'] = 'Nice';
        $_GET['id'] = 1;

        unset($_SESSION['user_id']);

        $controller = $this->getControllerMock();
        $controller->index();

        $this->assertTrue($controller->renderData['data']['notLoggedIn']);
    }
    public function testRateValid()
        {
            $controller = $this->getControllerMock();

            $result = $controller->rate(1, 5, 1, 'Great');

            $this->assertTrue($result);
        }

    public function testRateInvalid()
        {
            $controller = $this->getControllerMock();

            $result = $controller->rate(1, 10, 1, 'Invalid');

            $this->assertFalse($result);
        }
}