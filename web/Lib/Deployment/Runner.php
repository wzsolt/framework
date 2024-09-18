<?php
namespace Framework\Deployment;

use Framework\Models\Database\Db;

abstract class Runner
{
    abstract public function run():bool;

    protected function db(string|false $databaseName = false):Db
    {
        return Db::create($databaseName);
    }

}