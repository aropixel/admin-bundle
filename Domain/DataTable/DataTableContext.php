<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

class DataTableContext
{
    private int $page;

    private int $length;

    private string $search;

    private DataTableColumn $orderColumn;

    private string $orderDirection;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @return DataTableColumn
     */
    public function getOrderColumn(): DataTableColumn
    {
        return $this->orderColumn;
    }

    /**
     * @return string
     */
    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }


}