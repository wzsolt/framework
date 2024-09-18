<?php

namespace Framework\Components\Lists;

class ListDbTable extends AbstractList
{
    private string $sql;

    private string $preprocessor;

    public function setOptions(string $sql, string $preprocessor):self
    {
        $this->sql = $sql;

        $this->preprocessor = $preprocessor;

        return $this;
    }

    protected function setup(): array
    {
        return $this->listFromSqlQuery($this->sql, $this->preprocessor);
    }

}