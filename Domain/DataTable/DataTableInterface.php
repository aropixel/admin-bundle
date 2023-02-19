<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

interface DataTableInterface
{
    public function getRows() : array;
    public function getTotal() : int;
    public function getResponse() : array;
}