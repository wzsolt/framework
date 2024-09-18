<?php

use Framework\Components\HostConfig;
use Framework\Components\Uuid;
use Framework\Models\Database\Db;

include DOC_ROOT . 'config/api-constants.php';

$data = [];
$db = Db::create();
$host = HostConfig::create();

$result = $db->getRows(
    Db::select(
        'api_users',
        [
            'au_id AS id',
            'au_auth_type AS type',
            'au_expiry AS expiry',
            'au_name AS name',
            'au_api_key AS apiKey',
            'au_last_request AS lastRequest',
            'au_services AS services',
            'au_enabled AS enabled',
            'au_username AS userName',
            'au_ip_whitelist AS ipWhitelist',
            'au_failedlogins AS failedLogins',
            'au_last_request AS lastRequest',
        ],
        [
            'au_client_id' => $host->getClientId()
        ]
    )
);
if ($result) {
    foreach ($result as $row) {
        $data['users'][$row['id']] = $row;

        if(!Empty($row['services'])) {
            $data['users'][$row['id']]['serviceList'] = explode('|', trim($row['services'], '|'));
            $data['users'][$row['id']]['services'] = str_replace('|', ', ', trim($row['services'], '|'));
        }
        if(!Empty($row['ipWhitelist'])) {
            $data['users'][$row['id']]['ipWhitelist'] = explode('|', trim($row['ipWhitelist'], '|'));
        }
    }
}

$data['editMode'] = false;

if (!Empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->sqlQuery(
        Db::delete(
            'api_users',
            [
                'au_id' => $id,
                'au_client_id' => $host->getClientId(),
            ]
        )
    );

    header('Location: /tools/?page=api');
    exit();
}

if (isset($_REQUEST['edit'])) {
    $data['errors'] = [];
    $data['editMode'] = true;
    $id = (int)$db->escapeString($_REQUEST['edit']);

    if (!Empty($_POST['save'])) {
        $data['api'] = $_POST['api'];

        if (Empty($data['api']['type'])) $data['api']['type'] = 0;
        if (Empty($data['api']['enabled'])) $data['api']['enabled'] = 0;
        if (Empty($data['api']['expiry'])) $data['api']['expiry'] = -1;
        if (Empty($data['api']['serviceList'])) $data['api']['serviceList'] = [];

        if (empty($data['api']['name'])) {
            $data['errors']['name'] = true;
        }
        if (empty($data['api']['serviceList'])) {
            $data['errors']['serviceList'] = true;
        }

        if (!$id && $data['api']['type'] == 0) {
            if (empty($data['api']['userName'])) {
                $data['errors']['userName'] = true;
            }

            if (empty($data['api']['password'])) {
                $data['errors']['password'] = true;
            }
            if (empty($data['api']['password2'])) {
                $data['errors']['password2'] = true;
            }
            if (!empty($data['api']['password']) && !empty($data['api']['password2']) && $data['api']['password'] != $data['api']['password2']) {
                $data['errors']['password'] = true;
                $data['errors']['password2'] = true;
            }
        }

        if ($id && isset($data['users'][$id])) {
            foreach ($data['users'][$id] as $key => $value) {
                if (!isset($data['api'][$key])) {
                    $data['api'][$key] = $value;
                }
            }
        }

        if (!$data['errors']) {
            $data['api']['serviceList'] = implode('|', $data['api']['serviceList']);
            $data['api']['ipWhitelist'] = str_replace("\r", '', trim($data['api']['ipWhitelist']));
            $data['api']['ipWhitelist'] = str_replace("\n", '|', $data['api']['ipWhitelist']);

            if (!$id) {
                $db->sqlQuery(
                    Db::insert(
                        'api_users',
                        [
                            'au_client_id' => $host->getClientId(),
                            'au_name' => $data['api']['name'],
                            'au_auth_type' => $data['api']['type'],
                            'au_api_key' => ($data['api']['type'] == 1 ? Uuid::v4() : ''),
                            'au_expiry' => ($data['api']['expiry'] == -1 ? null : $data['api']['expiry']),
                            'au_username' => $data['api']['userName'],
                            'au_password' => ($data['api']['type'] == 0 ? password_hash($data['api']['password'], PASSWORD_DEFAULT) : ''),
                            'au_services' => $data['api']['serviceList'],
                            'au_ip_whitelist' => $data['api']['ipWhitelist'],
                            'au_enabled' => $data['api']['enabled'],
                        ]
                    )
                );
            } else {
                $db->sqlQuery(
                    Db::update(
                        'api_users',
                        [
                            'au_name' => $data['api']['name'],
                            'au_expiry' => ($data['api']['expiry'] == -1 ? null : $data['api']['expiry']),
                            'au_services' => $data['api']['serviceList'],
                            'au_ip_whitelist' => $data['api']['ipWhitelist'],
                            'au_enabled' => $data['api']['enabled'],
                        ],
                        [
                            'au_id' => $id,
                            'au_client_id' => $host->getClientId()
                        ]
                    )
                );
            }

            header('Location: /tools/?page=api');
            exit();
        }

    } elseif (isset($data['users'][$id])) {
        $data['api'] = $data['users'][$id];
    } else {
        $id = 0;
    }

    if (!Empty($_POST['changePwd']) && $id && Empty($_POST['api']['type'])) {
        $data['pwd'] = $_POST['pwd'];

        if (empty($data['pwd']['password'])) {
            $data['errors']['password'] = true;
        }
        if (empty($data['pwd']['password2'])) {
            $data['errors']['password2'] = true;
        }
        if (!empty($data['pwd']['password']) && !empty($data['pwd']['password2']) && $data['pwd']['password'] != $data['pwd']['password2']) {
            $data['errors']['password'] = true;
            $data['errors']['password2'] = true;
        }

        if (!$data['errors']) {
            $db->sqlQuery(
                Db::update(
                    'api_users',
                    [
                        'au_password' => password_hash($data['pwd']['password'], PASSWORD_DEFAULT),
                    ],
                    [
                        'au_id' => $id,
                        'au_client_id' => $host->getClientId(),
                    ]
                )
            );

            header('Location: /tools/?page=api&edit=' . $id . '&success=1');
            exit();
        }
    }

    $data['id'] = $id;
    $data['version'] = API_CURRENT_VERSION;
    $data['services'] = $GLOBALS['API_SERVICES'];
    $data['success'] = !Empty($_REQUEST['success']);
}

