<?php

namespace Framework\Components\Lists;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

class ListMenuGroups extends AbstractList
{
    protected function setup(): array
    {
        return $this->listFromSqlQuery(
            Db::select(
                'menu',
                [
                    'm_title AS value',
                    'm_id AS `key`'
                ],
                [
                    'm_client_id' => HostConfig::create()->getClientId(),
                    'm_language' => HostConfig::create()->getLanguage(),
                    //'m_visible' => 1,
                    'm_url' => '',
                ],
                [],
                false,
                'm_order'
            )
        );
    }
}