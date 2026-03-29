<?php

abstract class Pagination
{
    protected array $items;
    protected int   $perPage;
    protected int   $totalItems;
    protected int   $totalPages;
    protected int   $currentPage;
    protected array $currentItems;

    public function __construct(array $items, int $perPage = 8)
    {
        $this->items      = $items;
        $this->perPage    = max(1, $perPage);
        $this->totalItems = count($items);
        $this->totalPages = (int) ceil($this->totalItems / $this->perPage);

        $page               = isset($_GET['p']) ? (int) $_GET['p'] : 1;
        $this->currentPage  = max(1, min($this->totalPages ?: 1, $page));

        $offset             = ($this->currentPage - 1) * $this->perPage;
        $this->currentItems = array_slice($items, $offset, $this->perPage);
    }

    // Retourne les éléments de la page courante
    public function getCurrentItems(): array
    {
        return $this->currentItems;
    }

    // Navigation HTML
    public function getNavigationLinks(string $baseUrl = '?'): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination-nav">';

        if ($this->currentPage > 1) {
            $prev  = $this->currentPage - 1;
            $html .= '<a href="' . htmlspecialchars($baseUrl . 'p=' . $prev) . '" class="pagination-btn" aria-label="Page précédente">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
                </svg>
            </a>';
        }

        $html .= '<span class="pagination-info">Page ' . $this->currentPage . ' sur ' . $this->totalPages . '</span>';

        if ($this->currentPage < $this->totalPages) {
            $next  = $this->currentPage + 1;
            $html .= '<a href="' . htmlspecialchars($baseUrl . 'p=' . $next) . '" class="pagination-btn" aria-label="Page suivante">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                </svg>
            </a>';
        }

        $html .= '</div>';
        return $html;
    }

    // Getters
    public function getCurrentPage(): int { return $this->currentPage; }
    public function getTotalPages(): int  { return $this->totalPages;  }
    public function getTotalItems(): int  { return $this->totalItems;  }

    // Méthode abstraite (les sous-classes nomment ses éléments comme elle le souhaite)
    abstract public function getCurrentElements(): array;
}