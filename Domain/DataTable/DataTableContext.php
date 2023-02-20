<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

class DataTableContext
{
    private string $search;

    private int $page;

    private int $length;

    private int $orderColumn;

    private string $orderDirection;

    private array $additionalParameters = [];


    /**
     * @param string $search
     * @param int $page
     * @param int $length
     * @param int $orderColumn
     * @param string $orderDirection
     */
    public function __construct(string $search, int $page, int $length, int $orderColumn, string $orderDirection)
    {
        $this->search = $search;
        $this->page = $page;
        $this->length = $length;
        $this->orderColumn = $orderColumn;
        $this->orderDirection = $orderDirection;
    }


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
     * @return int
     */
    public function getOrderColumn(): int
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

    public function addParameters(array $additionalParameters)
    {
        $this->additionalParameters = array_merge($this->additionalParameters, $additionalParameters);
    }
}