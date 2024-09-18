<?php

namespace Applications\Admin\Controllers;

use Framework\Components\Menu\AbstractMenuBuilder;
use Framework\Components\Menu\MenuItem;

class Menu extends AbstractMenuBuilder
{
    public function setup(): void
    {
        $this->addItems(
            (new MenuItem('index', true, MenuItem::MENU_POSITION_HIDDEN)),
            (new MenuItem('ajax', false, MenuItem::MENU_POSITION_HIDDEN)),
            (new MenuItem('login', false, MenuItem::MENU_POSITION_HIDDEN))->setLayout('login'),
            (new MenuItem('logout', false, MenuItem::MENU_POSITION_HIDDEN)),
            (new MenuItem('download', false, MenuItem::MENU_POSITION_HIDDEN)),
            (new MenuItem('set-new-password', false, MenuItem::MENU_POSITION_HIDDEN))->setLayout('login'),
            (new MenuItem('my-profile', true, MenuItem::MENU_POSITION_HEADER))
                ->setIcon('ri-account-circle-line'),

            (new MenuItem('settings', true, MenuItem::MENU_POSITION_SIDEBAR))
                ->setIcon('fa-light fa-fw fa-wrench')
                ->addItems(

                    (new MenuItem('system', true, MenuItem::MENU_POSITION_SIDEBAR))
                        ->setIcon('fa-light fa-fw fa-cog')
                        ->addItems(

                            (new MenuItem('hosts', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                            (new MenuItem('user-access-level', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                            (new MenuItem('administrators', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                            (new MenuItem('site-settings', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                        ),


                    (new MenuItem('content', true, MenuItem::MENU_POSITION_SIDEBAR))
                        ->setIcon('fa-light fa-fw fa-book')
                        ->addItems(

                            //(new MenuItem('menu', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                            //(new MenuItem('pages', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                            (new MenuItem('dictionary', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS),
                            (new MenuItem('templates', true, MenuItem::MENU_POSITION_SIDEBAR))->setUserGroups(USER_GROUP_ADMINISTRATORS)
                        ),
                ),

            (new MenuItem('page-not-found', false, MenuItem::MENU_POSITION_HIDDEN))
        );
    }
}