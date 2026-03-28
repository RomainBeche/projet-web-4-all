<?php

use Grp5\ProjetWeb4All\Controllers\{
    HomeController,
    OffersController,
    CompaniesController,
    RegistrationController,
    AccountController,
    FavoritesController,
};

return [
    'accueil' => ['controller' => HomeController::class, 'action' => 'index'],
    'annonces' => ['controller' => OffersController::class, 'action' => 'index'],
    'entreprises' => ['controller' => CompaniesController::class, 'action' => 'index'],
    'creation-compte' => ['controller' => RegistrationController::class, 'action' => 'index'],
    'compte' => ['controller' => AccountController::class, 'action' => 'index'],
    'favoris' => ['controller' => FavoritesController::class, 'action' => 'index'],
    'modification-compte' => ['controller' => AccountController::class, 'action' => 'edit'],
    'deconnexion' => ['controller' => AccountController::class, 'action' => 'logoutConfirmation'],
    'suppression-compte-1' => ['controller' => AccountController::class, 'action' => 'deleteConfirmation'],
    'suppression-compte-2' => ['controller' => AccountController::class, 'action' => 'delete'],
    'modification-compte-validation' => ['controller' => AccountController::class, 'action' => 'editValidation'],
    'login' => ['controller' => AccountController::class,'action' => 'login'],
    'logout' => ['controller' => AccountController::class, 'action' => 'logout'],
    /*
    'annonces' => ['controller' => OfferController::class, 'action' => 'list'],
    'detail-annonce' => ['controller' => OfferController::class, 'action' => 'detail'],
    'creation-compte-etudiant' => ['controller' => AccountController::class, 'action' => 'createStudent'],
    'creation-compte-pilote' => ['controller' => AccountController::class, 'action' => 'createPilot'],
    'creation-entreprise' => ['controller' => AccountController::class, 'action' => 'createCompany'],
    'fiche-entreprise' => ['controller' => CompanyController::class, 'action' => 'detail'],
    // Add all your other routes here... */
];