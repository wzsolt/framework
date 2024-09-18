<?php

namespace Framework\Components\Lists;

use Framework\Components\Enums\AccessLevel;

class ListAccessOptions extends AbstractList
{
    private string $class = '';

    protected function setup(): array
    {
        return [
            AccessLevel::NoAccess->value => [
                'icon'  => 'fa fa-times fa-fw',
                'color' => 'danger',
                'label' => 'LBL_NO_ACCESS',
                'class' => $this->class,
            ],
            AccessLevel::Readonly->value => [
                'icon'  => 'fa fa-eye fa-fw',
                'color' => 'primary',
                'label' => 'LBL_READ_ONLY',
                'class' => $this->class,
            ],
            AccessLevel::ReadAndWrite->value => [
                'icon'  => 'fa fa-pencil fa-fw',
                'color' => 'warning',
                'label' => 'LBL_READ_WRITE_ACCESS',
                'class' => $this->class,
            ],
            AccessLevel::FullAccess->value => [
                'icon'  => 'fa fa-check fa-fw',
                'color' => 'success',
                'label' => 'LBL_FULL_ACCESS',
                'class' => $this->class,
            ],
        ];
    }

    public function setParams(array $params): self
    {
        $this->class = ($params['class'] ?? '');

        return $this;
    }

}