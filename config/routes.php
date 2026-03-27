<?php

use Grp5\ProjetWeb4All\Controllers\{
    AccueilController,
};

return [
    'accueil' => ['controller' => AccueilController::class, 'action' => 'index']
    /*'login' => ['controller' => AuthController::class, 'action' => 'login'],
    'logout' => ['controller' => AuthController::class, 'action' => 'logout'],
    'annonces' => ['controller' => OfferController::class, 'action' => 'list'],
    'detail-annonce' => ['controller' => OfferController::class, 'action' => 'detail'],
    'creation-compte-etudiant' => ['controller' => AccountController::class, 'action' => 'createStudent'],
    'creation-compte-pilote' => ['controller' => AccountController::class, 'action' => 'createPilot'],
    'creation-entreprise' => ['controller' => AccountController::class, 'action' => 'createCompany'],
    'fiche-entreprise' => ['controller' => CompanyController::class, 'action' => 'detail'],
    // Add all your other routes here... */
];