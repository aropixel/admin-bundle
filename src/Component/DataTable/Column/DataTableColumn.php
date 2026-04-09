<?php

namespace Aropixel\AdminBundle\Component\DataTable\Column;

class DataTableColumn
{
    /**
     * @var string Html classes for column
     */
    private string $htmlClasses = '';

    /**
     * @param array<string,string> $data Custom html data attributes to add to column
     */
    public function __construct(
        private readonly string $label,
        private readonly string $orderBy,
        private readonly string $style = '',
        private readonly array $data = []
    ) {
    }

    public static function fromArray(array $config): self
    {
        $column = new self(
            $config['label'] ?? '',
            $config['orderBy'] ?? $config['field'] ?? '',
            $config['style'] ?? '',
            $config['data'] ?? []
        );

        if (isset($config['class'])) {
            $column->setClass($config['class']);
        }

        return $column;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @return string[]
     */
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
