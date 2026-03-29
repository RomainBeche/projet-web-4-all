<?php
class PaginationAnnonces extends Pagination
{
    public function getCurrentElements(): array
    {
        return $this->getCurrentItems();
    }
}