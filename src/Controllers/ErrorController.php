<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class ErrorController extends Controller
{
    public function notFound(): void
    {
        $this->render('pages/404.twig.html');
    }
}

?>