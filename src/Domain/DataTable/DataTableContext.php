<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

class DataTableContext
{
    private array $additionalParameters = [];

    public function __construct(
        private readonly string $search,
        private readonly int $draw,
        private readonly int $start,
        private readonly int $length,
        private readonly int $orderColumn,
        private readonly string $orderDirection
    ) {
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function getOrderColumn(): int
    {
        return $this->orderColumn;
    }

    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    public function getDraw(): int
    {
        return $this->draw;
    }

    public function addParameters(array $additionalParameters)
    {
        $this->additionalParameters = array_merge($this->additionalParameters, $additionalParameters);
    }

    public function getAdditionalParameters(): array
    {
        return $this->additionalParameters;
    }

    public function getAdditionalParameter(string $keyName)
    {
        return \array_key_exists($keyName, $this->additionalParameters) ? $this->additionalParameters[$keyName] : null;
    }
}
