<?php

namespace Framework\Components\Lists;

use Framework\Components\Functions;
use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

class ListUsers extends AbstractList
{
    protected function setup(): array
    {
        return $this->listFromSqlQuery(
            Db::select(
                'users',
                [
                    'us_id AS `key`',
                    'us_code AS code',
                    'CONCAT(us_firstname, " ", us_lastname) AS value',
                    'ug_id AS groupId',
                    'ug_name AS groupName'
                ],
                [
                    'us_client_id' => HostConfig::create()->getClientId(),
                    'us_enabled' => 1,
                    'us_deleted' => 0
                ],
                [
                    'user_groups' => [
                        'on' => [
                            'ug_id' => 'us_ug_id'
                        ]
                    ]
                ],
                'us_code'
            ),

            'maskName'
        );
    }

    protected function maskName(array $row):array
    {
        $row['value'] = Functions::getName($row['code'], $row['value']);

        return $row;
    }
}