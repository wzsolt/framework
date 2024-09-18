<?php

namespace Framework\Components;

use Framework\Components\Enums\AccessLevel;
use Framework\Helpers\Utils;
use Framework\Models\Database\Db;
use Framework\Models\Memcache\MemcachedHandler;
use Framework\Models\Session\Session;

class User
{
    private static User $instance;

    private bool $isLoggedIn = false;

    private int $id = 0;

    private string $hash;

    private int $superUserId = 0;

    private ?string $group;

    private ?string $role;

    private bool $isSuperUser = false;

    private ?string $code;

    private int $groupId = 0;

    // private string $groupName;

    private string $name;

    private ?string $lastName;

    private ?string $firstName;

    private ?string $title;

    private string $email;

    private ?string $phone;

    private ?string $country;

    private ?string $zipCode;

    private ?string $city;

    private ?string $address;

    private ?string $lastLogin;

    private ?string $registered;

    private int $timezone = 0;

    private ?string $profileImage;

    private array $user = [];

    public static function create():User
    {
        if (!isset(self::$instance)) {
            self::$instance = new User();
        }

        return self::$instance;
    }

    public function load():User
    {
        $user = Session::get(SESSION_USER);

        if(!Empty($user)) {
            $this->set($user, false);
        }

        return $this;
    }

    public function get():array
    {
        return Session::get(SESSION_USER);
    }

    public function changeSession(string $key, mixed $value):void
    {
        $user = Session::get(SESSION_USER);
        $user[$key] = $value;

        $this->setUserSession($user);
    }

    public static function getUserProfile(int $userId, bool $forceLoad = false):array
    {
        $memcache = MemcachedHandler::create();

        $user = $memcache->get(CACHE_USER_PROFILE . $userId);

        if (!$user || $forceLoad ) {
            $user = Db::create()->getFirstRow(
                Db::select(
                    'users',
                    [
                        'us_id AS id',
                        'us_code AS code',
                        'us_hash AS hash',
                        'us_email AS email',
                        'us_phone AS phone',
                        'us_title AS title',
                        'us_firstname AS firstName',
                        'us_lastname AS lastName',
                        'us_img AS img',
                        'us_enabled AS isEnabled',
                        'us_group AS userGroup',
                        'us_role AS userRole'
                    ],
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

            if(!$user) {
                $user = [
                    'id' => $userId,
                    'isMissing' => true
                ];
            }else{
                $user['name'] = Utils::localizeName($user['firstName'], $user['lastName'], HostConfig::create()->getLanguage());
                //$user['displayName'] = Utils::getDisplayName($user['code'], $user['name']);

                $user['hasProfilePicture'] = ($user['img'] != '');
                $user['img'] = User::setProfilePicture($user);
            }

            $memcache->set(CACHE_USER_PROFILE . $userId, $user);
        }

        return $user;
    }

    public function login(string $email, string $password, string|false $userGroup = false):bool
    {
        $user = Db::create()->getFirstRow(
            Db::select(
                'users',
                [
                    'us_id AS id',
                    'us_password AS password',
                    'us_group AS userGroup',
                    'us_role AS role',
                ],
                [
                    'us_deleted' => 0,
                    'us_enabled' => 1,
                    'us_email' => $email,
                    'us_client_id' => HostConfig::create()->getClientId(),
                ]
            )
        );

        if (!empty($user) && password_verify($password, $user['password'])) {

            if(!Empty($userGroup) && $user['userGroup'] !== $userGroup){
                return false;
            }

            return $this->loginWithId($user['id']);
        }

        return false;
    }

    /**
     * @param string $page
     * @param AccessLevel $level
     * @return bool
     */
    public function hasPageAccess(string $page, AccessLevel $level = AccessLevel::ReadAndWrite):bool
    {
        return (!empty($this->user['accessRights'][$page])
            && $this->user['accessRights'][$page] >= $level->value);
    }

    public function getAccessLevel(string $page):AccessLevel
    {
        $accessLevel = (int)($this->user['accessRights'][$page] ?? 0);

        return AccessLevel::from($accessLevel);
    }

    /**
     * @param string $function
     * @return bool
     */
    public function hasFunctionAccess(string $function):bool
    {
        if($this->user['functionRights']){
            if(in_array($function, $this->user['functionRights'])){
                return true;
            }
        }

        return false;
    }

    /**
     * Checks weather the user is logged in with the given role
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles):bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if(in_array($this->role, $roles)) {
            return true;
        }

        return false;
    }


    /**
     * Checks weather the user is logged in with the given type
     * @param string|array $types
     * @return bool
     */
    public function hasGroup(string|array $types):bool
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        if(in_array($this->group, $types)) {
            return true;
        }

        return false;
    }

    public function validateUserByEmail(string $email):array
    {
        $result = [
            'isValid' => false,
            'userId' => 0
        ];

        $user = Db::create()->getFirstRow(
            Db::select(
                'users',
                [
                    'us_id',
                ],
                [
                    'us_deleted' => 0,
                    'us_enabled' => 1,
                    'us_email' => $email
                ]
            )
        );

        if($user){
            $result = [
                'isValid' => true,
                'userId' => $user['us_id'],
            ];
        }

        return $result;
    }

    private function createLoginToken(int $userId, string $type = 'NEWPWD', int $expiryHours = 8):string
    {
        $token = Utils::generateRandomString(50, false);

        $expiry = time() + (60 * 60 * $expiryHours);

        Db::create()->sqlQuery(
            Db::insert(
                'tokens',
                [
                    'tk_id' => $token,
                    'tk_us_id' => $userId,
                    'tk_expire' => date('Y-m-d H:i:s', $expiry),
                    'tk_type' => $type,
                    'tk_used' => 0,
                    'tk_auto_login' => (in_array($type, ['REGISTER', 'LOGIN']) ? 1 : 0)
                ]
            )
        );

        return $token;
    }

    /**
     * Create pwd change token link
     * @param int $userId
     * @param string $type NEWPWD|REGISTER|LOGIN
     * @param string|false $page default: 'set-new-password'
     * @param int $expiryHours hours
     * @return string
     */
    public function getPasswordChangeLink(int $userId, string $type = 'NEWPWD', string|false $page = false, int $expiryHours = 8): string
    {
        $hostConfig = HostConfig::create();

        if(!$type){
            $type = 'NEWPWD';
        }

        if(!$page){
            $page = $hostConfig->getDomain() . 'set-new-password';

            /**
             * @todo localize page name from AbstractMenuBuilder
             */

            /*
            if($hostConfig->getApplication() === 'Admin'){
                $page = $GLOBALS['PAGE_NAMES']['en']['set-new-password']['name'];
            }else {
                $page = $GLOBALS['PAGE_NAMES'][$hostConfig->getLanguage()]['set-new-password']['name'];
            }
            */
        }

        return $page . '/?token=' . urlencode($this->createLoginToken($userId, $type, $expiryHours));
    }

    public function checkToken(string $token, bool $setUsed = false):array
    {
        $valid = [
            'isValid' => false
        ];

        $token = urldecode($token);

        $res = Db::create()->getFirstRow(
            Db::select(
                'tokens',
                [],
                [
                    'tk_id' => $token,
                    'tk_used' => 0,
                    'tk_expire' => [
                        'greater' => 'NOW()'
                    ]
                ]
            )
        );

        if($res){
            $valid = [
                'isValid'   => true,
                'userId'    => $res['tk_us_id'],
                'token'     => $res['tk_id'],
                'type'      => $res['tk_type'],
                'expire'    => $res['tk_expire'],
                'isAutoLogin' => $res['tk_auto_login']
            ];

            if($setUsed){
                Db::create()->sqlQuery(
                    Db::update(
                        'tokens',
                        [
                            'tk_used' => 1
                        ],
                        [
                            'tk_id' => $res['tk_id'],
                            'tk_us_id' => $res['tk_us_id']
                        ]
                    )
                );
            }
        }

        return $valid;
    }

    public function setPassword(int $userId, string $password):void
    {
        Db::create()->sqlQuery(
            Db::update(
                'users',
                [
                    'us_password' => password_hash($password, PASSWORD_DEFAULT),
                    'us_force_pwd_change' => 0
                ],
                [
                    'us_id' => $userId
                ]
            )
        );
    }

    public function getProperty(string $key):mixed
    {
        return ($this->user[$key] ?? null);
    }

    public function loginWithId(int $id, bool $superUser = false):bool
    {
        $superUserId = 0;

        if($superUser && $this->isSuperUser()){
            $superUserId = $this->superUserId;
        }

        $this->reset();

        $user = $this->loadUser($id);

        $needAuthentication = $this->checkLoginHistory($id);

        if (!empty($user)) {
            $this->set($user);

            if($superUser && $superUserId) {
                $this->isSuperUser = true;
                $this->superUserId = $superUserId;
            }else{
                Db::create()->sqlQuery(
                    Db::update(
                        'users',
                        [
                            'us_last_login' => 'NOW()'
                        ],
                        [
                            'us_id' => $user['id']
                        ]
                    )
                );
            }

            return true;
        }

        return false;
    }

    private function checkLoginHistory(int $userId):bool
    {
        $db = Db::create();

        $need2Fa = false;

        $isMobile = false;

        //$isMobile = $this->isMobileView();

        $ipData = Functions::getLocationByIp();

        $browser = get_browser(NULL, true);
        $hash = md5($browser['platform'] . $browser['browser'] . ($ipData['countryCode'] ?? '') . $ipData['ip'] . HostConfig::create()->getMachineId());

        $row = $db->getFirstRow(
            Db::select(
                'user_logins',
                [],
                [
                    'ul_us_id' => $userId,
                    'ul_hash' => $hash,
                    'ul_expire>' => 'NOW()',
                ]
            )
        );

        if($row) {
            $db->sqlQuery(
                Db::update(
                    'user_logins',
                    [
                        'ul_last_used' => 'NOW()',
                        'ul_logins' => 'INCREMENT',
                    ],
                    [
                        'ul_us_id' => $userId,
                        'ul_hash' => $hash
                    ]
                )
            );
        }else{
            $db->sqlQuery(
                Db::insert(
                    'user_logins',
                    [
                        'ul_us_id' => $userId,
                        'ul_hash' => $hash,
                        'ul_ip' => ($ipData['ip'] ?? ''),
                        'ul_country' => ($ipData['countryCode'] ?? ''),
                        'ul_mobile' => ($isMobile ? 1 : 0),
                        'ul_browser' => $browser['browser'],
                        'ul_browser_ver' => $browser['version'],
                        'ul_os' => $browser['platform'],
                        'ul_last_used' => 'NOW()',
                        'ul_expire' => date('Y-m-d H:i:s', time() + (60 * 60 * 24 * 365)),
                        'ul_logins' => 1,
                        'ul_validated' => 0
                    ],
                    ['ul_us_id', 'ul_hash']
                )
            );

            $need2Fa = $hash;
        }

        return $need2Fa;
    }

    private function loadUser(int $id):array
    {
        $user = [];

        $data = Db::create()->getFirstRow(
            Db::select(
                'users',
                [],
                [
                    'us_id' => $id,
                    'us_enabled' => 1,
                    'us_deleted' => 0,
                ]
            )
        );

        if (!empty($data)) {
            unset(
                $data['us_password']
            );

            foreach ($data as $key => $val) {
                $prefix = substr($key, 0, 2);
                $subKey = substr($key, 3);

                switch($prefix){
                    case 'us':
                    default:
                        $key = 'user';
                        break;
                }

                $user[$subKey] = $val;
            }

            $user['name'] = Utils::localizeName($user['firstname'], $user['lastname'], HostConfig::create()->getLanguage());

            $user['profileImage'] = $this->setProfilePicture($user);

            $user['accessRights'] = $this->getAccessLevels(
                HostConfig::create()->getApplication(),
                $user['group'],
                $user['role']
            );

            $user['functionRights'] = $this->getFunctionRights(
                HostConfig::create()->getApplication(),
                $user['group'],
                $user['role']
            );
        }

        return $user;
    }

    private function setUserSession(array $user):void
    {
        Session::set(SESSION_USER, $user);
    }

    private function set(array $user, bool $updateSession = true):void
    {
        $this->isLoggedIn = true;

        $this->id = (int)$user['id'];

        $this->hash = $user['hash'];

        $this->code = $user['code'];

        $this->groupId = (int) $user['ug_id'];

        if(!Empty($user['supervisor'])){
            $this->superUserId = $user['id'];

            $this->isSuperUser = true;
        }

        $this->group = ($user['group'] ?? USER_GROUP_ADMINISTRATORS);

        $this->role = ($user['role'] ?? USER_ROLE_SUPERVISOR);

        HostConfig::create()->setTimeZoneCode($user['timezone']);

        $this->user = $user;

        $this->name = $user['name'];
        $this->firstName = $user['firstname'];
        $this->lastName = $user['lastname'];
        $this->title = $user['title'];
        $this->email = $user['email'];
        $this->phone = ($user['phone'] ?? null);
        $this->country = ($user['country'] ?? null);
        $this->zipCode = ($user['zip'] ?? null);
        $this->city = ($user['city'] ?? null);
        $this->address = ($user['address'] ?? null);
        $this->lastLogin = ($user['last_login'] ?? null);
        $this->registered = ($user['registered'] ?? null);
        $this->timezone = ($user['timezone'] ?? null);
        $this->profileImage = ($user['profileImage'] ?? null);

        if($updateSession) {
            $this->setUserSession($user);
        }
    }

    private function reset():void
    {
        $this->isSuperUser = false;

        $this->id = 0;

        $this->superUserId = 0;

        $this->group = '';

        $this->role = '';

        $this->user = [];
    }

    private static function setProfilePicture(array $user):string
    {
        if($user['img']){
            $src = FOLDER_UPLOAD . 'users/' . $user['hash'] . '/profile/' . $user['img'];
        }else{
            if(Empty($user['title'])) $user['title'] = 'MR';

            $src = '/images/' . strtolower($user['title']) . '.svg';
        }

        return $src;
    }

    private function getAccessLevels(string $app, string $userGroup, string $userRole):array
    {
        $rights = [];

        $accessRights = DB::create()->getRows(
            Db::select(
                'access_levels',
                [
                    'al_page',
                    'al_right',
                ],
                [
                    'al_app' => $app,
                    'al_group' => $userGroup,
                    'al_role' => $userRole,
                ]
            )
        );

        if($accessRights) {
            foreach ($accessRights as $row) {
                if (empty($rights[$row['al_page']]) || $rights[$row['al_page']] < $row['al_right']) {
                    $rights[$row['al_page']] = $row['al_right'];
                }
            }
        }

        return $rights;
    }

    private function getFunctionRights(string $app, string $userGroup, string $userRole):array
    {
        $rights = [];

        $functionRights = Db::create()->getRows(
            Db::select(
                'access_function_rights',
                [
                    'afr_key',
                ],
                [
                    'afr_app' => $app,
                    'afr_group' => $userGroup,
                    'afr_role' => $userRole,
                ]
            )
        );
        if($functionRights) {
            foreach ($functionRights as $row) {
                $rights[] = $row['afr_key'];
            }
        }

        return $rights;
    }

    /**
     * Validate user password
     * @param string $password
     * @param false|string $email
     * @return bool
     */
    public function validatePassword(string $password, false|string $email = false):bool
    {
        $where = [
            'us_id' => $this->getId(),
            'us_enabled' => 1,
            'us_deleted' => 0,
        ];

        if($email){
            $where['us_email'] = $email;
        }

        $user = Db::create()->getFirstRow(
            Db::select(
                'users',
                [
                    'us_password'
                ],
                $where
            )
        );
        if($user && password_verify($password, $user['us_password'])){
            return true;
        }

        return false;
    }

    public function isSuperUser():bool
    {
        return $this->isSuperUser;
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getGroup(): ?string
    {
        return ($this->group ?? null);
    }

    public function getRole(): ?string
    {
        return ($this->role ?? null);
    }

    public function getCode(): string
    {
        return ($this->code ?? '');
    }

    public function getName(): string
    {
        return ($this->name ?? '');
    }

    public function getLastName(): string
    {
        return ($this->lastName ?? '');
    }

    public function getFirstName(): string
    {
        return ($this->firstName ?? '');
    }

    public function getTitle(): string
    {
        return ($this->title ?? '');
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getZipCode(): int
    {
        return $this->zipCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLastLogin(): string
    {
        return $this->lastLogin;
    }

    public function getRegistered(): string
    {
        return $this->registered;
    }

    public function getTimezone(): int
    {
        return $this->timezone;
    }

    public function getProfileImage(): string
    {
        return ($this->profileImage ?? '');
    }

    public function changeProfileImage(string $image):void
    {
        $this->profileImage = $this->setProfilePicture([
            'hash' => $this->hash,
            'title' => $this->title,
            'img' => $image,
        ]);

        $this->changeSession('profileImage', $this->profileImage);
    }

    public function clearUserDataCache(int $userId):void
    {
        MemcachedHandler::create()->delete(CACHE_USER_PROFILE . $userId);
    }
}