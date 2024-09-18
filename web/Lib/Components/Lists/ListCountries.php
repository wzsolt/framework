<?php

namespace Framework\Components\Lists;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

class ListCountries extends AbstractList
{

    protected function setup(): array
    {
        $hostConfig = HostConfig::create();

        return $this->listFromSqlQuery(
            Db::select(
                'countries',
                [
                    'country_code AS `key`',
                    'country_name_' . (in_array($hostConfig->getLanguage(), $hostConfig->getLanguages()) ? strtolower($hostConfig->getLanguage()) : 'en') . ' AS value'
                ],
                [],
                [],
                false,
                'value'
            )
        );
    }

}