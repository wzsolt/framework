<?php
namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Applications\Admin\Controllers\Tables\HostsTable;

class Hosts extends AbstractPageConfig
{
    public function setup(): ?array
    {

        $hosts = new HostsTable();

        $this->setVariable(
            'table',
            $this->addTable($hosts)
        );

        $this->setTemplate('page-table');

        return null;
    }
}