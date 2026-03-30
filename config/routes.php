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
    RateCompanyController,
    LegalController,
    ApplyController,
    MyApplicationsController,
    MyApplicationController,

};

return [
    'accueil' => ['controller' => HomeController::class, 'action' => 'index'],

    'login' => ['controller' => AccountController::class, 'action' => 'login'],
    'compte' => ['controller' => AccountController::class, 'action' => 'index'],
    'modification-compte' => ['controller' => AccountController::class, 'action' => 'edit'],
    'modification-compte-validation' => ['controller' => AccountController::class, 'action' => 'editValidation'],
    'deconnexion' => ['controller' => AccountController::class, 'action' => 'logout'],
    'suppression-compte-1' => ['controller' => AccountController::class, 'action' => 'deleteConfirmation'],
    'suppression-compte-2' => ['controller' => AccountController::class, 'action' => 'delete'],
    
    'creation-compte' => ['controller' => RegistrationController::class, 'action' => 'index'],
    
    'favoris' => ['controller' => FavoritesController::class, 'action' => 'index'],

    'annonces' => ['controller' => OffersController::class, 'action' => 'index'],
    'detail-annonce' => ['controller' => OfferDetailsController::class, 'action' => 'index'],
    'toggle-favori' => ['controller' => OfferDetailsController::class, 'action' => 'toggleFavorite'],
    'toggle-rappel' => ['controller' => OfferDetailsController::class, 'action' => 'toggleReminder'],
    
    'entreprises' => ['controller' => CompaniesController::class, 'action' => 'index'],
    'detail-entreprise' => ['controller' => CompanyDetailsController::class, 'action' => 'index'],
    'evaluation-entreprise' => ['controller' => RateCompanyController::class, 'action' => 'index'],
    
    'mentions-legales' => ['controller' => LegalController::class, 'action' => 'index'],
    
    'postuler' => ['controller' => ApplyController::class, 'action' => 'index'],
    'postuler-store'  => ['controller' => MyApplicationController::class, 'action' => 'store'],
    
    'mes-candidatures' => ['controller' => MyApplicationsController::class, 'action' => 'index'],
    'ma-candidature'  => ['controller' => MyApplicationController::class, 'action' => 'index'],

    'mes-eleves' => ['controller' => AccountController::class, 'action' => 'mesEleves'],
    'mes-eleves-creation' => ['controller' => AccountController::class, 'action' => 'mesElevesCreation'],


    

    /*'logout' => ['controller' => AuthController::class, 'action' => 'logout'],
    'deconnexion' => ['controller' => AccountController::class, 'action' => 'logoutConfirmation'],
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