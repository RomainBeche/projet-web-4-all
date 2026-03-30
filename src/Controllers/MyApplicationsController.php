<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class MyApplicationsController extends Controller
{
    public function index(): void
    {
        $this->render('pages/mes-candidatures.twig.html');
    }
}