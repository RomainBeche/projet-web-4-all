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
    'entreprises-gestion' => ['controller' => AccountController::class, 'action' => 'entreprisesGestion'],
    'creation-entreprise' => ['controller' => AccountController::class, 'action' => 'creationEntreprise'],
    'modification-entreprise' => ['controller' => AccountController::class, 'action' => 'modificationEntreprise'],
    'suppression-entreprise' => ['controller' => AccountController::class, 'action' => 'suppressionEntreprise'],
    
    'favoris' => ['controller' => FavoritesController::class, 'action' => 'index'],

    'annonces' => ['controller' => OffersController::class, 'action' => 'index'],
    'detail-annonce' => ['controller' => OfferDetailsController::class, 'action' => 'index'],
    'toggle-favori' => ['controller' => FavoritesController::class, 'action' => 'toggle'],
    'creer-offre' => ['controller' => OffersController::class, 'action' => 'create'],
    
    'entreprises' => ['controller' => CompaniesController::class, 'action' => 'index'],
    'detail-entreprise' => ['controller' => CompanyDetailsController::class, 'action' => 'index'],
    'evaluation-entreprise' => ['controller' => RateCompanyController::class, 'action' => 'index'],
    
    'mentions-legales' => ['controller' => LegalController::class, 'action' => 'index'],
    
    'postuler' => ['controller' => ApplyController::class, 'action' => 'index'],
    'postuler-store'  => ['controller' => MyApplicationController::class, 'action' => 'store'],
    
    'mes-candidatures' => ['controller' => MyApplicationsController::class, 'action' => 'index'],
    'ma-candidature'  => ['controller' => MyApplicationController::class, 'action' => 'index'],
    'mes-annonces' => ['controller' => OffersController::class, 'action' => 'mesAnnonces'],

    'mes-eleves' => ['controller' => AccountController::class, 'action' => 'mesEleves'],
    'mes-eleves-creation' => ['controller' => AccountController::class, 'action' => 'mesElevesCreation'],
    'mes-eleves-detail' => ['controller' => AccountController::class, 'action' => 'mesElevesDetail'],
    'gestion-eleves' => ['controller' => AccountController::class, 'action' => 'gestionEleves'],
    'modification-eleve' => ['controller' => AccountController::class, 'action' => 'modificationEleve'],
    'suppression-eleve' => ['controller' => AccountController::class, 'action' => 'suppressionEleve'],




    


];