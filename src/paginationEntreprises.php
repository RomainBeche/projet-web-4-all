<?php
class Pagination {
    private $entreprises;           // Liste complète des offres
    private $perPage;               // Éléments par page
    private $totalEntreprises;      // Total offres
    private $totalPages;            // Total pages
    private $currentPage;           // Page actuelle (sécurisée)
    private $currentEntreprises;    // Offres de la page courante

    public function __construct(array $entreprises, int $perPage = 8) {
        $this->entreprises = $entreprises;
        $this->perPage = max(1, $perPage);  // Minimum 1
        $this->totalEntreprises = count($entreprises);
        $this->totalPages = ceil($this->totalEntreprises / $this->perPage);

        // Sécurisation page
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $this->currentPage = max(1, min($this->totalPages, $page));

        // Découpage avec array_slice (offset, length)
        $offset = ($this->currentPage - 1) * $this->perPage;
        $this->currentEntreprises = array_slice($entreprises, $offset, $this->perPage);
    }

    // Retourne les offres de la page courante
    public function getCurrentEntreprises(): array {
        return $this->currentEntreprises;
    }

    // Génère les liens de navigation HTML
    public function getNavigationLinks(string $baseUrl = '?'): string {
        $html = '<div class="pagination-nav">';

        // Précédent
        if ($this->currentPage > 1) {
            $prev = $this->currentPage - 1;
            $html .= '<a href="' . htmlspecialchars($baseUrl . 'p=' . $prev) . '" class="pagination-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                    <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                </svg>
            </a>';
        }

        // Info page
        $html .= '<span class="pagination-info">Page ' . $this->currentPage . ' sur ' . $this->totalPages . '</span>';

        // Suivant
        if ($this->currentPage < $this->totalPages) {
            $next = $this->currentPage + 1;
            $html .= '<a href="' . htmlspecialchars($baseUrl . 'p=' . $next) . '" class="pagination-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16">
                    <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z"/>
                </svg>
            </a>';
        }

        $html .= '</div>';
        return $html;
}


    // Getters utiles
    public function getCurrentPage(): int { return $this->currentPage; }
    public function getTotalPages(): int { return $this->totalPages; }
}
?>