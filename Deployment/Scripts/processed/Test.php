<?php

namespace Runners;

use Framework\Components\HostConfig;
use Framework\Deployment\Runner;
use Framework\Models\Database\Db;

class Test extends Runner
{

    public function run(): bool
    {
        echo "Running test...\n";

        return true;
    }
}