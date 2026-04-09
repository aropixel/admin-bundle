<?php

namespace Aropixel\AdminBundle\Component\DataTable\Context;

class DataTableContext
{
    /**
     * @var array<mixed>
     */
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

    /**
     * @param array<mixed> $additionalParameters
     */
    public function addParameters(array $additionalParameters): void
    {
        $this->additionalParameters = array_merge($this->additionalParameters, $additionalParameters);
    }

    /**
     * @return array<mixed>
     */
    public function getAdditionalParameters(): array
    {
        return $this->additionalParameters;
    }

    public function getAdditionalParameter(string $keyName): mixed
    {
        return \array_key_exists($keyName, $this->additionalParameters) ? $this->additionalParameters[$keyName] : null;
    }
}
