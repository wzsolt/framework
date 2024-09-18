<?php

namespace Framework\Components\Lists;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

class ListTemplateTypes extends AbstractList
{
    protected function setup(): array
    {
        return $this->listFromSqlQuery(
            Db::select(
                'templates',
                [
                    'mt_key AS `key`',
                    'CONCAT("LBL_TEMPLATE_", mt_key) AS value'
                ],
                [
                    'mt_client_id' => HostConfig::create()->getClientId()
                ],
                [],
                'value')
        );
    }

}