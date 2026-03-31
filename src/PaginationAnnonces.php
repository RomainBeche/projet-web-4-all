<?php

require_once __DIR__ . '/Pagination.php';

class PaginationAnnonces extends Pagination
{
    public function getCurrentElements(): array
    {
        return $this->getCurrentItems();
    }
}