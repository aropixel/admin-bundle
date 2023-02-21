<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

class DataTableContext
{
    private string $search;

    private int $draw;

    private int $start;

    private int $length;

    private int $orderColumn;

    private string $orderDirection;

    private array $additionalParameters = [];


    /**
     * @param string $search
     * @param int $draw
     * @param int $start
     * @param int $length
     * @param int $orderColumn
     * @param string $orderDirection
     */
    public function __construct(string $search, int $draw, int $start, int $length, int $orderColumn, string $orderDirection)
    {
        $this->search = $search;
        $this->draw = $draw;
        $this->start = $start;
        $this->length = $length;
        $this->orderColumn = $orderColumn;
        $this->orderDirection = $orderDirection;
    }


    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
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

    /**
     * @return int
     */
    public function getDraw(): int
    {
        return $this->draw;
    }


    public function addParameters(array $additionalParameters)
    {
        $this->additionalParameters = array_merge($this->additionalParameters, $additionalParameters);
    }

    /**
     * @return array
     */
    public function getAdditionalParameters(): array
    {
        return $this->additionalParameters;
    }

    public function getAdditionalParameter(string $keyName)
    {
        return array_key_exists($keyName, $this->additionalParameters) ? $this->additionalParameters[$keyName] : null;
    }
}
