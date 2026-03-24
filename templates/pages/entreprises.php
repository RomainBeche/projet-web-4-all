{% extends "base.twig.html" %}

{% block title %}Créer une offre – Web4All{% endblock %}

{% block content %}


<?php include 'OffersDB.php'; ?>
<?php include 'Pagination.php'; ?>
<?php $pagination = new Pagination($offers, 8); ?>

<main class="page">
<section class="entreprises">
    <header class="entreprises-header">
    <h1>Les entreprises</h1>

    <div class="search-wrapper">
        <input type="search" placeholder="Bouygues Telecom..." class="search-input">
    </div>
    </header>

    <div class="cards-container">
    <div class="cards-window">
        <div class="cards-grid">
        
        <?php foreach ($pagination->getCurrentOffers() as $offer): ?>
        
            <article class="card">
            <div class="card-logo"><?= htmlspecialchars($offer['Logo']) ?></div>
            <p class="card-name"><?= htmlspecialchars($offer['Nom_entreprise']) ?></p>
            <p class="card-sector"><?= htmlspecialchars($offer['Secteur']) ?></p>
            <div class="card-rating"><?= htmlspecialchars($offer['Rating']) ?> ★ <span><?= htmlspecialchars($offer['Nombre_avis']) ?> Avis</span></div>
            <div class="card-footer">
                <button class="btn" onclick="window.location.href='404.twig.html'">Découvrir</button>
                <span class="offers"><?= htmlspecialchars($offer['Nombre_offres']) ?> Offres</span>
            </div>
            </article>

        <?php endforeach; ?>
        </div>
    </div>
    <?= $pagination->getNavigationLinks() ?>
    </div>
</section>
</main>
{% endblock %}