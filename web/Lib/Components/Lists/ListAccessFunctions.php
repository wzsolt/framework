<?php

namespace Framework\Components\Lists;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

class ListAccessFunctions extends AbstractList
{
    private string|false $group = false;

    protected function setup(): array
    {
        $where = [];
        $list = [];

        if($this->group){
            $where['af_group'] = $this->group;
        }

        $result = DB::create()->getRows(
            Db::select(
                'access_functions',
                [
                    'af_page AS page',
                    'af_name AS value',
                    'af_key AS `key`'
                ],
                $where,
                [],
                false,
                'value'
            )
        );
        if($result){
            foreach ($result as $row) {
                $list[$row['page']][] = [
                    'name' => $row['value'],
                    'key' => $row['key']
                ];
            }
        }

        return $list;
    }

    public function setParams(array $params): self
    {
        if(!Empty($params['group'])) $this->group = (string)$params['group'];

        return $this;
    }

}