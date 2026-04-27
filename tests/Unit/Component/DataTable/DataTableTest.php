<?php

namespace Aropixel\AdminBundle\Tests\Unit\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\DataTable\DataTable;
use Aropixel\AdminBundle\Component\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class DataTableTest extends TestCase
{
    private DataTable $dataTable;

    protected function setUp(): void
    {
        $context = $this->createMock(DataTableContext::class);
        $repository = $this->createMock(DataTableRepositoryInterface::class);
        $twig = $this->createMock(Environment::class);

        $this->dataTable = new DataTable(
            \stdClass::class,
            [],
            $context,
            $repository,
            $twig
        );
    }

    public function testSetModeXhr(): void
    {
        $this->dataTable->setMode(DataTableInterface::MODE_XHR);
        $this->assertSame(DataTableInterface::MODE_XHR, $this->dataTable->getMode());
    }

    public function testSetModeClassic(): void
    {
        $this->dataTable->setMode(DataTableInterface::MODE_CLASSIC);
        $this->assertSame(DataTableInterface::MODE_CLASSIC, $this->dataTable->getMode());
    }

    public function testSearchInReturnsFields(): void
    {
        $this->dataTable->searchIn(['title', 'slug']);
        $this->assertSame(['title', 'slug'], $this->dataTable->getSearchFields());
    }

    public function testOrderColumnAndDirection(): void
    {
        $this->dataTable->setOrderColumn(0);
        $this->dataTable->setOrderDirection('ASC');

        $this->assertSame(0, $this->dataTable->getOrderColumn());
        $this->assertSame('ASC', $this->dataTable->getOrderDirection());
    }
}
