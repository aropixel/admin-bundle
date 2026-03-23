<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepository;
use Aropixel\AdminBundle\Component\DataTable\Row\DataTableRowFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DataTable extends Response implements DataTableInterface
{
    /**
     * @var array<mixed>
     */
    private array $items = [];

    private string $mode = DataTableInterface::MODE_XHR;

    private ?int $orderColumn = null;

    private ?string $orderDirection = null;

    /**
     * @var callable|DataTableRowFactoryInterface|null
     */
    private $transformer = null;

    /**
     * @var string[]
     */
    private array $searchFields = [];

    /**
     * @var array<string, string>
     */
    private array $joins = [];

    /**
     * @var array<string, mixed>
     */
    private array $viewParameters = [];


    /**
     * @param class-string      $className
     * @param DataTableColumn[] $columns
     */
    public function __construct(
        private readonly string $className,
        private array $columns,
        private readonly DataTableContext $context,
        private readonly DataTableRepositoryInterface $dataTableRepository,
        private readonly Environment $twig
    ) {
        parent::__construct();
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getOrderColumn(): ?int
    {
        return $this->orderColumn;
    }

    public function setOrderColumn(?int $orderColumn): self
    {
        $this->orderColumn = $orderColumn;

        return $this;
    }

    public function getOrderDirection(): ?string
    {
        return $this->orderDirection;
    }

    public function setOrderDirection(?string $orderDirection): self
    {
        $this->orderDirection = $orderDirection;

        return $this;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = [];
        foreach ($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    public function addColumn(array|DataTableColumn $column): self
    {
        if (is_array($column)) {
            $column = DataTableColumn::fromArray($column);
        }

        $this->columns[] = $column;

        return $this;
    }

    public function addColumnsIf(bool $condition, array $columns): self
    {
        if ($condition) {
            foreach ($columns as $column) {
                $this->addColumn($column);
            }
        }

        return $this;
    }

    public function useRepositoryMethod(string $methodName): self
    {
        if ($this->dataTableRepository instanceof DataTableRepository) {
            $this->dataTableRepository->setRepositoryMethod($methodName);
        }

        return $this;
    }

    public function filter(callable $filter): self
    {
        if ($this->dataTableRepository instanceof DataTableRepository) {
            $this->dataTableRepository->addFilter($filter);
        }

        return $this;
    }

    public function searchIn(array $fields): self
    {
        $this->searchFields = $fields;

        return $this;
    }

    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    public function join(string $property, string $alias): self
    {
        $this->joins[$property] = $alias;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    public function renderJson(callable $transformer): self
    {
        if ($this->context->getDraw() > 0) {

            $count = $this->getTotal();

            $records = [];
            $records['data'] = $this->getRows($transformer);
            $records['order'] = [];
            $records['draw'] = $this->context->getDraw();
            $records['recordsTotal'] = $count;
            $records['recordsFiltered'] = $count;

            $this->setContent(json_encode($records));
            $this->headers->set('Content-Type', 'application/json');
        }

        $this->transformer = $transformer;

        return $this;
    }

    public function render(string $template, array $parameters = []): self
    {
        if ($this->context->getDraw() == 0) {

            $this->viewParameters = array_merge($parameters, $this->viewParameters);
            $this->viewParameters['dataTable'] = $this;

            $content = $this->twig->render($template, $this->viewParameters);

            $this->setContent($content);
        }

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getContext(): DataTableContext
    {
        return $this->context;
    }

    /**
     * @return DataTableColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return mixed[]
     */
    public function getRowsContent(): iterable
    {
        return $this->dataTableRepository->getRowsContent($this);
    }

    /**
     * @param callable|DataTableRowFactoryInterface $transformer
     * @return array<array<string>>
     */
    public function getRows(callable|DataTableRowFactoryInterface $transformer): array
    {
        $rows = [];
        $rowsContent = $this->getRowsContent();

        foreach ($rowsContent as $rowContent) {
            if ($transformer instanceof DataTableRowFactoryInterface) {
                $rows[] = $transformer->createRow($rowContent);
            } else {
                $rows[] = $transformer($rowContent);
            }
        }

        return $rows;
    }

    public function getTotal(): int
    {
        return $this->dataTableRepository->count($this);
    }

    public function getResponse(callable|DataTableRowFactoryInterface $transformer): Response
    {
        $count = $this->getTotal();

        $records = [];
        $records['data'] = $this->getRows($transformer);
        $records['order'] = [];
        $records['draw'] = $this->context->getDraw();
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;

        $http_response = new Response(json_encode($records));
        $http_response->headers->set('Content-Type', 'application/json');

        return $http_response;
    }
}
