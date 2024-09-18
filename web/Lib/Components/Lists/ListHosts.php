<?php

namespace Framework\Components\Lists;

use Framework\Models\Database\Db;

class ListHosts extends AbstractList
{

    protected function setup(): array
    {
        return $this->listFromSqlQuery(
            Db::select(
                'hosts',
                [
                    'host_id AS `key`',
                    'host_name AS value'
                ],
                [],
                [],
                'host_id',
                'host_name'
            )
        );
    }

}