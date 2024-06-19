<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

class DataTableColumn
{
    /**
     * @var string Html classes for column
     */
    private string $htmlClasses = '';

    public function __construct(
        private readonly string $label,
        private readonly string $queryField,
        private readonly string $style = '',
        private readonly array $data = []
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getQueryField(): string
    {
        return $this->queryField;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getClass(): string
    {
        return $this->htmlClasses;
    }

    public function setClass(string $htmlClasses): self
    {
        $this->htmlClasses = $htmlClasses;

        return $this;
    }
}
