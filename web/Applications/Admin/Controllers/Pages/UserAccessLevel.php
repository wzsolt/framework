<?php

namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Framework\Components\Enums\Color;
use Framework\Components\Lists\ListAccessFunctions;
use Framework\Components\Lists\ListAccessOptions;
use Framework\Components\Lists\ListApplications;
use Framework\Components\Lists\ListUserGroups;
use Framework\Components\Lists\ListUserRoles;
use Framework\Components\Menu\AbstractMenuBuilder;
use Framework\Components\Menu\MenuItem;
use Framework\Components\User;
use Framework\Controllers\Buttons\ButtonDropdown;
use Framework\Controllers\Forms\Inputs\InputCheckbox;
use Framework\Controllers\Forms\Inputs\InputSelect;
use Framework\Models\Database\Db;
use Framework\Models\Session\Session;
use Framework\Router;

class UserAccessLevel extends AbstractPageConfig
{
    public function setup(): ?array
    {
        $data = Session::get('ual-selection');

        $userGroups = (new ListUserGroups())->getList();

        $applications = (new ListApplications())->getList();

        if(isset($_REQUEST['change'])){
            if(isset($_REQUEST['group']) && isset($userGroups[$_REQUEST['group']])) {
                $data['group'] = $_REQUEST['group'];
            }

            if(isset($_REQUEST['app']) && isset($applications[$_REQUEST['app']])) {
                $data['app'] = $_REQUEST['app'];
            }

            Session::set('ual-selection', $data);
            Router::pageRedirect('/settings/system/user-access-level/');
        }

        if(empty($data['group']) || !isset($userGroups[$data['group']])) {
            $data['group'] = USER_GROUP_ADMINISTRATORS;
        }

        if(empty($data['app']) || !isset($applications[$data['app']])) {
            $data['app'] = 'admin';
        }

        $menuClass = '\\Applications\\' . $data['app'] . '\\Controllers\\Menu';

        if(class_exists($menuClass)){
            /**
             * @var $menu AbstractMenuBuilder
             */

            $menu = new $menuClass();

            $data['menu'] = $this->collectAccessRestrictedMenuItems( $menu->buildMenu($data['group']), ['index']);
        }

        $data['roles'] = (new ListUserRoles())->setParams(['group' => $data['group']])->getList();

        $data['accessRights'] = $this->getAccessRights($data['app'], $data['group']);

        $data['functions'] = (new ListAccessFunctions())->setParams(['group' => $data['group']])->getList();

        $data['functionRights'] = $this->getFunctionRights($data['app'],$data['group']);

        $data['controls']['appSelector'] = (new InputSelect('app', 'LBL_APPLICATION'))
                                                ->addAttribute('onchange', 'this.form.submit();')
                                                ->setOptions($applications);

        $data['controls']['groupSelector'] = (new InputSelect('group'))
                                                ->addAttribute('onchange', 'this.form.submit();')
                                                ->setOptions($userGroups);

        $data['controls']['accessSelector'] = (new ButtonDropdown('access', false))
                                                ->addClass('btn-xs')
                                                ->setOptions((new ListAccessOptions())->setParams(['class' => 'btn-access-level'])->getList());

        $data['controls']['functionSelector'] = (new InputCheckbox('function'))
                                                ->setColor(Color::Success);

        return $data;
    }

    private function getAccessRights(string $app, string $group):array
    {
        $data = [
            'currentLevel' => User::create()->getAccessLevel('user-access-level')->value
        ];

        $res = Db::create()->getRows(
            Db::select(
                'access_levels',
                [],
                [
                    'al_app' => $app,
                    'al_group' => $group,
                ],
                [],
                false,
                'al_page'
            )
        );

        foreach($res as $row) {
            $data[$row['al_role']][$row['al_page']] = (int) $row['al_right'];
        }

        return $data;
    }

    private function getFunctionRights(string $app,string $group):array
    {
        $data = [
            'currentLevel' => User::create()->getAccessLevel('user-access-level')->value
        ];

        $res = Db::create()->getRows(
            Db::select(
                'access_function_rights',
                [],
                [
                    'afr_app' => $app,
                    'afr_group' => $group
                ],
                [],
                false,
                'afr_page'
            )
        );

        foreach ($res as $row) {
            $data[$row['afr_role']][$row['afr_key']] = 1;
        }

        return $data;
    }

    private function collectAccessRestrictedMenuItems(array $items, array $exclude = []):array
    {
        $menu = [];

        /**
         * @var $item MenuItem
         */

        foreach($items AS $key => $item){
            if($item->isRequireLogin() && !in_array($key, $exclude)){
                $menu[$key] = $item;

                if ($item->isContainer()) {
                    $menu[$key]->setItems($this->collectAccessRestrictedMenuItems($item->getItems(), $exclude));
                }
            }
        }

        return $menu;
    }

}