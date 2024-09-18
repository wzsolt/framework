<?php

namespace Framework\Components\Lists;

class ListNumberRange extends AbstractList
{
    private int $start;

    private int $end;

    public function setOptions(int $start, int $end):self
    {
        $this->start = $start;

        $this->end = $end;

        return $this;
    }

    protected function setup(): array
    {
        return array_combine(range($this->start, $this->end), range($this->start, $this->end));
    }

}