<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class LegalController extends Controller
{
    public function index(): void
    {
        $this->render('pages/mentions-legales.twig.html');
    }
}