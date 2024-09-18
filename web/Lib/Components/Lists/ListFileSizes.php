<?php

namespace Framework\Components\Lists;

use Framework\Helpers\File;

class ListFileSizes extends AbstractList
{
    protected function setup(): array
    {
        $list = [];

        $maxSize = File::getFileUploadMaxSize() / pow(1024, 2);
        for ($i = 1; $i <= $maxSize; $i++) {
            $list[$i] = $i . ' Mb';
        }

        return $list;
    }

}