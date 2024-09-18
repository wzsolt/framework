<?php
namespace Applications\Admin\Controllers\Ajax;

use Framework\Components\Enums\PageType;
use Framework\Components\User;
use Framework\Controllers\Pages\AbstractAjaxConfig;
use Framework\Models\Database\Db;

class AccessRights extends AbstractAjaxConfig
{
    public function setup(): bool
    {
        if(User::create()->hasPageAccess('user-access-level')){
            $db = Db::create();

            $app = $db->escapestring($_REQUEST['app']);
            $group = $db->escapestring($_REQUEST['group']);
            $role = $db->escapestring($_REQUEST['role']);
            $right = (int)$db->escapestring($_REQUEST['value']);
            $page = $db->escapestring($_REQUEST['page']);
            $function = $db->escapestring(($_REQUEST['function'] ?? ''));

            if(Empty($group) && Empty($role) && Empty($page)) return false;

            if (!$function) {
                if (empty($right)) {
                    $db->sqlQuery(
                        Db::delete(
                            'access_levels',
                            [
                                'al_app' => $app,
                                'al_group' => $group,
                                'al_page' => $page,
                                'al_role' => $role,
                            ]
                        )
                    );

                } else {
                    $db->sqlQuery(
                        Db::insert(
                            'access_levels',
                            [
                                'al_app' => $app,
                                'al_role' => $role,
                                'al_group' => $group,
                                'al_page' => $page,
                                'al_right' => $right,
                            ],
                            [
                                'al_app',
                                'al_role',
                                'al_group',
                                'al_page',
                            ]
                        )
                    );
                }
            } else {
                if (empty($_REQUEST['checked'])) {
                    $db->sqlQuery(
                        Db::delete(
                            'access_function_rights',
                            [
                                'afr_app' => $app,
                                'afr_group' => $group,
                                'afr_page' => $page,
                                'afr_role' => $role,
                                'afr_key' => $function,
                            ]
                        )
                    );

                } else {
                    $db->sqlQuery(
                        Db::insert(
                            'access_function_rights',
                            [
                                'afr_app' => $app,
                                'afr_group' => $group,
                                'afr_page' => $page,
                                'afr_role' => $role,
                                'afr_key' => $function,
                            ]
                        )
                    );
                }
            }

        }

        return true;
    }

    protected function setAction(?array $params = [], array $post = [], ?array $rawInput = []): string|false
    {
        return false;
    }

    protected function setOutputFormat(): PageType
    {
        return PageType::Json;
    }
}