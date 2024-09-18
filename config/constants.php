<?php
const APPLICATION_NAME      = 'Camo';
const APPLICATION_VERSION   = '1.0';
const DEFAULT_TIMEZONE_ID   = 29;            // (UTC+01:00) Belgrade, Bratislava, Budapest, Ljublj


// session keys
const SESSION_USER          = 'camo-userdata';
const SESSION_LOCALE        = 'camo-locale';
const SESSION_MESSAGES      = 'camo-messages';
const SESSION_HTACCESS      = 'camo-htaccess';

const LOGIN_COOKIE_NAME     = 'camo-login';

const HOST_SETTINGS         = APPLICATION_NAME . '-hosts-';
const LABELS_KEY            = APPLICATION_NAME . '-labels-';

// content cache keys
const CACHE_SETTINGS        = APPLICATION_NAME . INSTANCE_ID . '-settings-';
const CACHE_USER_PROFILE    = APPLICATION_NAME . INSTANCE_ID . '-userprofile-';
const CACHE_SYNCED_TABLES   = APPLICATION_NAME . INSTANCE_ID . '-sync-tables-';

// cookie keys
const COOKIE_MACHINE_ID     = 'camo-mid';

const VERSION_JS            = APPLICATION_VERSION . '.00';
const VERSION_CSS           = APPLICATION_VERSION . '.00';

$GLOBALS['APPLICATIONS'] = [
    'Admin'     => 'LBL_APPLICATION_ADMIN',
    //'Api'     => 'LBL_APPLICATION_API',
];

$GLOBALS['THEMES'] = [
    'minton' => [
        'name' => 'Minton',
        'css' => [
            //'roboto'      => 'roboto/roboto.min.css',
            'jquery-ui'     => 'jquery-ui/jquery-ui.min.css',
            'fontawesome'   => 'fontawesome-pro-6.1.1/css/all.min.css',
            'flatpickr'     => 'flatpickr/flatpickr.min.css',
            'select2'       => 'select2/css/select2.min.css',
            'spectrum'      => 'spectrum-colorpicker2/spectrum.min.css',
            'tagsinput'     => 'bootstrap-tagsinput/bootstrap-tagsinput.css',
        ],
        'js' => [
            'jquery'        => 'jquery/jquery-3.6.0.min.js',
            'jqueryUi'      => 'jquery-ui/jquery-ui.min.js',
            'bootstrap'     => 'bootstrap/5.0.2/bootstrap.bundle.min.js', // older version used due to multiple modals (BS 5.1+ not supported multiple modals)
            'simplebar'     => 'simplebar/simplebar.min.js',
            'select2'       => 'select2/js/select2.js',
            //'feather'     => 'feather-icons/feather.min.js',
            'autocomplete'  => 'autocomplete/bootstrap-autocomplete.min.js',
            'inputmask'     => 'inputmask/jquery.inputmask.min.js',
            'inputmask-binding'   => 'inputmask/bindings/inputmask.binding.js',
            'zoom'          => 'zoom/jquery.zoom.min.js',
            'flatpickr'     => 'flatpickr/flatpickr.min.js',
            //'flatpickr-lng'   => 'flatpickr/l10n/' . $this->owner->language . '.js',
            'bootstrap-select'   => 'bootstrap-select/bootstrap-select.js',
            //'bootstrap-select-lng'   => 'bootstrap-select/js/i18n/defaults-' . strtolower($this->owner->language) . '_' . strtoupper($this->owner->language) . '.js',
            'tagsinput'     => 'bootstrap-tagsinput/bootstrap-tagsinput.js',
            'typeahead'     => 'bootstrap-tagsinput/typeahead.bundle.js',
            'spectrum'      => 'spectrum-colorpicker2/spectrum.min.js',
            'chart'         => 'chart/dist/Chart.min.js',
            'peity'         => 'Peity/jquery.peity.min.js',
        ]
    ],
];


//
/**
 * d - day of month (no leading zero)
 * dd - day of month (two digit)
 * D - day name short
 * DD - day name long
 * m - month of year (no leading zero)
 * mm - month of year (two digit)
 * M - month name short
 * MM - month name long
 * y - year (two digit)
 * yy - year (four digit)
 */
$GLOBALS['REGIONAL_SETTINGS'] = [
	'en' => [
		'name'          => 'English',
		'text'          => 'ltr',
		'firstday'      => 1,
		'dateformat'    => 'yy-mm-dd',
		'dateformat_short' => 'd. M.',
		'dateorder'     => 'ymd',
		'timeformat'    => '24',
		'decimal_point' => '.',
		'thousand_sep'  => ',',
		'currency_round'  => 1,
		'nameorder'     => 'first-last'
	],
	'hu' => [
		'name'          => 'Magyar',
		'text'          => 'ltr',
		'firstday'      => 1,
		'dateformat'    => 'yy-mm-dd',
		'dateformat_short' => 'M. d.',
		'dateorder'     => 'ymd',
		'timeformat'    => '24',
		'decimal_point' => ',',
		'thousand_sep'  => ' ',
		'currency_round'  => 0,
		'nameorder'     => 'last-first'
	],
];
$GLOBALS['REGIONAL_SETTINGS']['default'] = $GLOBALS['REGIONAL_SETTINGS']['hu'];


$GLOBALS['UPLOAD_IMG_FILES'] = ['jpg', 'png', 'jpeg'];
$GLOBALS['UPLOAD_DOC_FILES'] = ['txt', 'pdf', 'doc', 'docx'];

$GLOBALS['ALLOWED_FILE_TYPES'] = [
    'pdf' => 'pdf',
    'doc' => 'doc',
    'txt' => 'txt',
    'xls' => 'xls',
    'jpg' => 'jpg',
    'png' => 'png',
];

$GLOBALS['ALLOWED_FILE_TYPES_ADDITIONAL'] = [
    'doc' => ['docx'],
    'xls' => ['xlsx'],
    'jpg' => ['jpeg'],
];

const TWIG_FILE_EXTENSION = '.twig';

$GLOBALS['PERSONAL_TITLES'] = [
	'MR',
	'MS',
	'MRS',
];

$GLOBALS['CURRENCIES'] = [
    'HUF' => [
        'sign' => 'Ft',
        'name' => 'forint',
        'round' => 0,
    ],
    'EUR' => [
        'sign' => '€',
        'name' => 'euro',
        'round' => 2,
    ],
    'USD' => [
        'sign' => '$',
        'name' => 'usdollar',
        'round' => 2,
    ],
];

const USER_GROUP_ADMINISTRATORS = 'ADMINISTRATORS';
const USER_GROUP_PILOTS = 'PILOTS';

$GLOBALS['USER_GROUPS'] = [
    USER_GROUP_ADMINISTRATORS => [
		'label' => 'LBL_GROUP_ADMINISTRATORS',
		'color' => 'danger',
		'app'   => 'admin',
	],
    USER_GROUP_PILOTS => [
        'label' => 'LBL_GROUP_PILOTS',
        'color' => 'info',
        'app'   => 'admin',
    ],
];

const USER_ROLE_SUPERVISOR  = 'SUPERVISOR';
const USER_ROLE_ADMIN       = 'ADMIN';
const USER_ROLE_USER        = 'USER';

$GLOBALS['USER_ROLES'] = [
    USER_GROUP_ADMINISTRATORS => [
        USER_ROLE_SUPERVISOR => [
            'label' => 'LBL_ROLE_SUPERVISOR',
            'color' => 'danger',
        ],
        USER_ROLE_ADMIN => [
            'label' => 'LBL_ROLE_ADMIN',
            'color' => 'warning',
        ],
        USER_ROLE_USER => [
            'label' => 'LBL_ROLE_USER',
            'color' => 'primary',
        ],
    ],
    USER_GROUP_PILOTS => [
        USER_ROLE_USER => [
            'label' => 'LBL_ROLE_USER',
            'color' => 'primary',
        ],
    ],
];

const IP_CACHE_TIMEOUT      = 7; // Days
const CHUNK_SIZE            = 1024 * 1024;

const PROFILE_IMG_SIZE      = 200;

const FILEUPLOAD_MAX_FILES  = 100;

const FILEUPLOAD_MAX_FILESIZE = 10; // Mb

const FILEUPLOAD_MAX_SIZE = 10000; // Mb


$GLOBALS['IMAGE_SIZES'] = [
    'large' => [
        'width'  => 1200,
        'height' => null,
        'crop' => false,
    ],
    'medium' => [
        'width'  => 800,
        'height' => null,
        'crop' => false,
    ],
    'thumbnail' => [
        'width'  => 192,
        'height' => 144,
        'crop' => true,
    ],
    'square' => [
        'width'  => 300,
        'height' => 300,
        'crop' => true,
    ],
];
