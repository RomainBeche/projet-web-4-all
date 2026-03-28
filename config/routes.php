<?php

use Grp5\ProjetWeb4All\Controllers\{
    HomeController,
    AccountController,
    RegistrationController,
    FavoritesController,
    OffersController,
    OfferDetailsController,
    CompaniesController,
    CompanyDetailsController,

};

return [
    'accueil' => ['controller' => HomeController::class, 'action' => 'index'],

    'compte' => ['controller' => AccountController::class, 'action' => 'index'],
    'modification-compte' => ['controller' => AccountController::class, 'action' => 'edit'],
    'modification-compte-validation' => ['controller' => AccountController::class, 'action' => 'editValidation'],
    'deconnexion' => ['controller' => AccountController::class, 'action' => 'logout'],
    'suppression-compte' => ['controller' => AccountController::class, 'action' => 'delete'],
    
    'creation-compte' => ['controller' => RegistrationController::class, 'action' => 'index'],
    
    'favoris' => ['controller' => FavoritesController::class, 'action' => 'index'],

    'annonces' => ['controller' => OffersController::class, 'action' => 'index'],
    'detail-annonce' => ['controller' => OfferDetailsController::class, 'action' => 'index'],
    
    'entreprises' => ['controller' => CompaniesController::class, 'action' => 'index'],
    'detail-entreprise' => ['controller' => CompanyDetailsController::class, 'action' => 'index'],
    'detail-annonce' => ['controller' => OfferDetailsController::class, 'action' => 'index'],
    'toggle-favori' => ['controller' => OfferDetailsController::class, 'action' => 'toggleFavorite'],
    'toggle-rappel' => ['controller' => OfferDetailsController::class, 'action' => 'toggleReminder'],



    /*'login' => ['controller' => AuthController::class, 'action' => 'login'],
    'logout' => ['controller' => AuthController::class, 'action' => 'logout'],
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