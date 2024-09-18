<?php

namespace Framework\Components\Lists;

class ListSyncTypes extends AbstractList
{

    protected function setup(): array
    {
        return $GLOBALS['SYNC_TYPES'];
    }
}