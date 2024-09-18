<?php
// directories
const DIR_ROOT              = '/var/www/framework/';
const DIR_WEB               = DIR_ROOT . 'web/';
const DIR_CACHE             = DIR_ROOT . 'var/cache/';
const DIR_LOG               = DIR_ROOT . 'var/logs/';
const DIR_UPLOAD            = DIR_WEB . 'public/uploads/';
const DIR_PRIVATE_UPLOAD    = DIR_ROOT . 'var/uploads/';

// Relative paths
const FOLDER_UPLOAD         = '/uploads/';

// database
const DB_TYPE               = 'mysql';
const DB_ENCODING           = 'UTF8';
const DB_NAME               = 'camo';
const DB_HOST               = 'localhost';
const DB_USER               = '';
const DB_PASSWORD           = '';

// memcache
const MEMCACHE_HOST         = 'localhost';
const MEMCACHE_PORT         = 11211;
const MEMCACHE_COMPRESS     = false;

// mongo DB
const MONGO_HOST            = 'localhost';
const MONGO_PORT            = 27017;
const MONGO_USERNAME        = false;
const MONGO_PASSWORD        = false;
const MONGO_DATABASE        = false;

// server related
const SERVER_ID             = 'production';
const INSTANCE_ID           = '-dgfkl4589g';
const DEBUG_ON              = false;
const TWIG_CACHE_ENABLED    = false;
const IMG_CACHE_ENABLED     = false;
const CHROME_BINARY         = 'chromium';

const EMAIL_SENDER_NAME     = 'framework';
const EMAIL_SENDER_EMAIL    = 'test@framework.com';
const EMAIL_INSTANT_SEND    = true;

// default environment values
const DEFAULT_HOST          = 'framework.test';
const DEFAULT_APPLICATION   = 'Admin';
const DEFAULT_THEME         = 'minton';
const DEFAULT_COUNTRY       = 'HU';
const DEFAULT_LANGUAGE      = 'en';
const DEFAULT_CURRENCY      = 'EUR';
const SERVER_TIME_ZONE      = 'Europe/Budapest'; //'UTC';

const IPAPI_KEY             = '';
const MAPS_API_KEY          = '';

const API_ENABLED           = false;
const API_HOST_NAME         = '';

const SECURE_SALT_KEY       = '';

const SYNC_DB_ENABLED           = false;
const SYNC_DB_SERVER_ENDPOINT   = '';
const SYNC_DB_API_USERNAME      = '';
const SYNC_DB_API_PASSWORD      = '';

const SAVE_LOGS             = true;

const CREATE_SCREENSHOT     = false;

const TIMEZONE_SOURCE       = 'Europe/Budapest';

const TIMEZONE_SERVER       = 'Europe/Budapest';
