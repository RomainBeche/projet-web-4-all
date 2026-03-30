<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class CompaniesController extends Controller
{
    public function index(): void {
        require_once __DIR__ . '/../../src/Models/Entreprises.php';
 
        $this->render('pages/entreprises.twig.html', [
            'entreprises' => $entreprises,
        ]);
        
    }

}
