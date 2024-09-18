<?php

namespace Framework\Components\Lists;

use Framework\Models\Database\Db;

class ListTimeZones extends AbstractList
{

    protected function setup(): array
    {
        return $this->listFromSqlQuery(
            Db::select(
                'timezones',
                [
                    'tz_id AS `key`',
                    'tz_name AS value'
                ],
                [],
                [],
                false,
                'tz_name'
            )
        );
    }

}