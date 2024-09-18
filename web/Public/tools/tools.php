<?php

use Framework\Models\Database\Db;

class Tools {
    public function __construct(){
    }

    public function getTables(){
        $tables = [];

        $result = Db::create()->getRows(
            'SHOW TABLES'
        );
        if($result){
            foreach($result AS $row){
                $value = reset($row);
                if($value !== 'trigger_log') {
                    $tables[] = $value;
                }
            }
        }

        return $tables;
    }

    public function getTriggers(){
        $triggers = [];

        $result = Db::create()->getRows(
            'SHOW TRIGGERS'
        );
        if($result){
            foreach($result AS $row){
                $triggers[$row['Table']][strtolower($row['Event'])] = 1;
            }
        }

        return $triggers;
    }
}