<?php

namespace Framework\Components\Menu;

class BaseMenu extends AbstractMenuBuilder
{

    public function setup(): void
    {
        $this->addItems(
            (new MenuItem('index', true, MenuItem::MENU_POSITION_HIDDEN)),
            (new MenuItem('ajax', false, MenuItem::MENU_POSITION_HIDDEN))
        );
    }
}