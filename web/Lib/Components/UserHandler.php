<?php
namespace Framework\Components;

use Framework\Facebook;

class UserHandler
{
    public function clearUserDataCache($userId){
        $this->owner->mem->delete(CACHE_USER_PROFILE . (int) $userId);
    }

    public function getUserProfile($userId, $forceLoad = false){
        $user = $this->owner->mem->get(CACHE_USER_PROFILE . (int) $userId);
        if (!$user || $forceLoad ) {
            $row = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'users',
                    [],
                    [
                        'us_deleted' => 0,
                        'us_id' => $userId,
                    ],
                    [],
                    false,
                    false,
                    1
                )
            );

            if(!$row) {
                $user['id'] = (int) $userId;
                $user['missing'] = true;
            }else{
                unset(
                    $row['us_password'],
                    $row['us_force_pwchange'],
                    $row['us_password_sent'],
                    $row['us_enabled'],
                    $row['us_deleted'],
                    $row['us_newsletter'],
                    $row['us_hash']
                );

                foreach ($row as $key => $val) {
                    $subKey = substr($key, 3);
                    $user[$subKey] = $val;
                }

                $user['name'] = localizeName($user['firstname'], $user['lastname'], $this->owner->language);
                $user['firstName'] = $user['firstname'];
                $user['lastName'] = $user['lastname'];
                $user['displayName'] = $this->owner->lib->getName($user['code'], $user['name']);

                $user['hasProfilePicture'] = ($user['img'] != '');
                $user['img'] = $this->setProfilePicture($user);
            }

            $this->owner->mem->set(CACHE_USER_PROFILE . $user['id'], $user);
        }

        return $user;
    }



	public function setGroup($group){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'users',
                [
                    'us_group' => $group,
                ],
                ['us_id' => $this->id]
            )
        );
        return $this;
    }

    public function setRole($role){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'users',
                [
                    'us_role' => $role,
                ],
                ['us_id' => $this->id]
            )
        );
        return $this;
    }

    public function setEnabled($enabled = true){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'users',
                [
                    'us_enabled' => ($enabled)
                ],
                ['us_id' => $this->id]
            )
        );
        return $this;
    }

	public function validateUserByUUID($uuid){
        $result = [
            'valid' => false
        ];
		$db = $this->owner->db;

		$user = $db->getFirstRow(
			"SELECT us_id FROM " . self::USERS_DB . ".users WHERE us_deleted = 0 AND us_enabled = 1 AND us_hash = '" . $db->escapestring($uuid) . "'"
		);

		if($user){
            $result = [
                'valid' => true,
                'userid' => $user['us_id'],
            ];
        }

		return $result;
	}

    public function validateUserFBId($id){
        $result = [
            'valid' => false
        ];
        $db = $this->owner->db;

        $user = $db->getFirstRow(
            "SELECT us_id FROM " . self::USERS_DB . ".users WHERE us_deleted = 0 AND us_enabled = 1 AND us_facebook_id = '" . $db->escapestring($id) . "'"
        );

        if($user){
            $result = [
                'valid' => true,
                'userid' => $user['us_id'],
            ];
        }

        return $result;
    }

    /*
    private function loginWithToken($userName, $userKey){
        $this->data = false;

        $user = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'users',
                [
                    'us_id',
                ],
                [
                    'us_username' => $userName,
                    'us_hash' => $userKey,
                    'us_client_id' => $this->owner->clientId,
                    'us_group' => USER_GROUP_STUDENTS,
                    'us_disable_login' => 0,
                    'us_enabled' => 1,
                    'us_deleted' => 0,
                ]
            )
        );
        if (!empty($user)) {
            $this->loginWithId($user['us_id']);
        }

        return $this->data;
    }
    */






	public function getTimezone($timeZone){
		$out = [];
        $timezone = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'timezones',
                [],
                [
                    'tz_id' => (int) $timeZone
                ]
            )
        );
		if (!empty($timezone)) {
			foreach ($timezone as $key => $val) {
				$out[substr($key, 3)] = $val;
			}
		}

		return $out;
	}

    public function deleteUser($userId){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLDelete(
                'users',
                [
                    'us_id' => $userId
                ]
            )
        );

        return true;
    }

    public function getFBLoginUrl($redirect = false){
        $url = false;
        if($this->owner->settings['facebookAppId']){
            if(!$redirect){
                $redirect = $this->owner->domain . 'oauth/login/';
            }

            $fb = new Facebook\Facebook([
                'app_id' => $this->owner->settings['facebookAppId'],
                'app_secret' => $this->owner->settings['facebookSecret'],
                'default_graph_version' => $this->owner->settings['facebookVersion'],
            ]);

            $helper = $fb->getRedirectLoginHelper();

            $permissions = ['email'];
            $url = $helper->getLoginUrl($redirect, $permissions);
        }

        return $url;
    }

    public function getFBProfilePicture($accessToken, $userid = 'me'){
        $error = false;
        $img = '';

        if($this->owner->settings['facebookAppId']){
            $fb = new Facebook\Facebook([
                'app_id' => $this->owner->settings['facebookAppId'],
                'app_secret' => $this->owner->settings['facebookSecret'],
                'default_graph_version' => $this->owner->settings['facebookVersion'],
            ]);

            try {
                $response = $fb->get('/' . $userid . '/picture?redirect=0&height=200&width=200&type=normal', $accessToken);
                $graphNode = $response->getDecodedBody();
            } catch(Facebook\Exception\FacebookResponseException $e) {
                // When Graph returns an error
                $error = 'Graph returned an error: ' . $e->getMessage();
            } catch(Facebook\Exception\FacebookSDKException $e) {
                // When validation fails or other local issues
                $error = 'Facebook SDK returned an error: ' . $e->getMessage();
            }

            if(!$error) {
                if ($graphNode['data']['url']) {
                    $img = $graphNode['data']['url'];
                } else {
                    $img = '';
                }
            }
        }

        return $img;
    }

    public function registerUser($data, $role = USER_ROLE_USER){
        $existingUser = $this->validateUserByEmail($data['email']);
        if($existingUser['valid']) {
            $userId = $existingUser['userid'];

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'users',
                    [
                        'us_facebook_id' => $data['id'],
                        'us_img' => $data['profile_img']
                    ],
                    [
                        'us_id' => $userId,
                    ]
                )
            );

        }else {
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLInsert(
                    'users',
                    [
                        'us_group' => USER_GROUP_STUDENTS,
                        'us_role' => $role,
                        'us_email' => $data['email'],
                        'us_lastname' => $data['last_name'],
                        'us_firstname' => $data['first_name'],
                        'us_facebook_id' => $data['id'],
                        'us_registered' => 'NOW()',
                        'us_language' => $this->owner->language,
                        'us_enabled' => 1,
                        'us_img' => $data['profile_img'],
                    ]
                )
            );
            $userId = $this->owner->db->getInsertRecordId();

            $data = [
                'id' => $userId,
                'link' => $this->setUserId($userId)->getPasswordChangeLink('REGISTER'),
            ];

            //$this->owner->email->prepareEmail('fb-register', $userId, $data);
        }

        return $userId;
    }
}
