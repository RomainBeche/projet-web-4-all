<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class RateCompanyController extends Controller
{
    public function index(): void
    {
        $this->render('pages/evaluation-entreprise.twig.html');
    }
}