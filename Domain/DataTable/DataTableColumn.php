<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

class DataTableColumn
{

    /**
     * @var string Column title
     */
    private string $label;

    /**
     * @var string Target field in query builder
     */
    private string $queryField;

    /**
     * @var string Inline style for column
     */
    private string $style;

    /**
     * @var array Html custom data-attributes
     * ["type" => "date-euro"] gives attribute data-type="date-euro"
     */
    private array $data;

    /**
     * @param string $label
     * @param string $queryField
     * @param string $style
     * @param array $data
     */
    public function __construct(string $label, string $queryField, string $style="", array $data=[])
    {
        $this->label = $label;
        $this->queryField = $queryField;
        $this->style = $style;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getQueryField(): string
    {
        return $this->queryField;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }



}