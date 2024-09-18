<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\ChangeAction;
use Framework\Components\Enums\Color;
use Framework\Components\Lists\ListApplications;
use Framework\Components\Lists\ListCountries;
use Framework\Components\Lists\ListCurrencies;
use Framework\Components\Lists\ListLanguages;
use Framework\Components\Lists\ListThemes;
use Framework\Components\Lists\ListTimeZones;
use Framework\Controllers\Buttons\ButtonModalClose;
use Framework\Controllers\Buttons\ButtonModalSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Containers\GroupRow;
use Framework\Controllers\Forms\Inputs\InputCheckGroup;
use Framework\Controllers\Forms\Inputs\InputPassword;
use Framework\Controllers\Forms\Inputs\InputSelect;
use Framework\Controllers\Forms\Inputs\InputSwitch;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Controllers\Forms\Sections\SectionTab;
use Framework\Helpers\Str;
use Framework\Helpers\Utils;
use Framework\Models\Memcache\MemcachedHandler;

class HostForm extends AbstractForm {

    public function setupKeyFields(): void
    {
        $this->setKeyField('host_id');
    }

    protected function setAccessLevel(): AccessLevel
    {
        return $this->user->getAccessLevel('hosts');
    }

    public function setup(): void
    {
        $this->setDatabaseTable('hosts');

        $this->setTitle('LBL_EDIT_HOST');

        $general = (new SectionTab('general', 'LBL_HOST_SETTINGS', '', true))
            ->addElements(
                (new InputText('host_name', 'LBL_HOST_SITE_NAME'))
                    ->setRequired(),
                (new InputText('host_host', 'LBL_HOST_NAME'))
                    ->setRequired()
                    ->setPrepend('https://'),
                (new InputText('host_public_site', 'LBL_PUBLIC_SITE')),
                (new InputText('host_default_email', 'LBL_DEFAULT_EMAIL'))
                    ->setRequired(),
                (new GroupRow('row1'))->addElements(
                    (new InputSelect('host_application', 'LBL_APPLICATION'))
                        ->setColSize('col-6')
                        ->setOptions((new ListApplications())->getList()),
                    (new InputSelect('host_theme', 'LBL_THEME'))
                        ->setColSize('col-6')
                        ->setOptions((new ListThemes())->getList())
                ),
                (new GroupRow('row0'))->addElements(
                    (new InputSelect('host_country', 'LBL_COUNTRY', DEFAULT_COUNTRY))
                        ->setColSize('col-6')
                        ->setOptions((new ListCountries())->getList()),
                    (new InputSelect('host_timezone', 'LBL_TIMEZONE', DEFAULT_TIMEZONE_ID))
                        ->setColSize('col-6')
                        ->setOptions((new ListTimeZones())->getList())
                ),
                (new GroupRow('row2'))->addElements(
                    (new InputSelect('host_default_language', 'LBL_DEFAULT_LANGUAGE', DEFAULT_LANGUAGE))
                        ->setColSize('col-6')
                        ->setOptions((new ListLanguages())->getList()),
                    (new InputSelect('host_default_currency', 'LBL_DEFAULT_CURRENCY', DEFAULT_CURRENCY))
                        ->setColSize('col-6')
                        ->setOptions((new ListCurrencies())->getList())
                ),
                (new GroupRow('row3'))->addElements(
                    (new InputCheckGroup('host_languages', 'LBL_SELECTABLE_LANGUAGES'))
                        ->setColSize('col-6')
                        ->setOptions((new ListLanguages())->getList()),
                    (new InputCheckGroup('host_currencies', 'LBL_SELECTABLE_CURRENCIES'))
                        ->setColSize('col-6')
                        ->setOptions((new ListCurrencies())->getList())
                ),
                (new InputSwitch('host_share_session', 'LBL_SHARE_SESSION_SUBDOMAINS'))
                    ->setGroupClass('mb-0'),
                (new InputSwitch('host_production', 'LBL_HOST_IS_PRODUCTION'))
                    ->setColor(Color::Danger),
                (new InputSwitch('host_maintenance', 'LBL_HOST_MAINTENANCE'))
                    ->setColor(Color::Danger)
        );

        $smtp = (new SectionTab('smtp', 'LBL_SMTP_SETTINGS'))
            ->addElements(
                (new InputText('host_smtp_host', 'LBL_SMTP_HOST')),
                (new GroupRow('row5'))->addElements(
                    (new InputSelect('host_smtp_port', 'LBL_SMTP_PORT', 2525))
                        ->setColSize('col-6')
                        ->setOptions([
                            'TLS' => [
                                25 => 25,
                                587 => 587,
                                2525 => 2525,
                                8025 => 8025,
                            ],
                            'SSL' => [
                                443 => 443,
                                465 => 465,
                                8465 => 8465,
                            ]
                        ]),
                    (new InputSelect('host_smtp_ssl', 'LBL_SMTP_SSL', 'TLS'))
                        ->setColSize('col-6')
                        ->setOptions([
                            'TLS' => 'TLS',
                            'SSL' => 'SSL',
                        ])
                ),
                (new GroupRow('row4'))->addElements(
                    (new InputText('host_smtp_user', 'LBL_SMTP_USER'))
                        ->setColSize('col-6'),
                    (new InputPassword('host_smtp_pwd', 'LBL_SMTP_PASSWORD'))
                        ->setColSize('col-6')
                        ->showTogglePassword()
                )
            );

        $httpAuth = (new SectionTab('auth', 'LBL_HTTP_AUTHENTICATION'))
            ->addElements(
                (new InputSwitch('host_protect', 'LBL_PROTECT_WEBSITE'))
                    ->changeState(1, ChangeAction::Editable, '#host_auth_user, #host_auth_password')
                    ->changeDefaultState(ChangeAction::Readonly, '#host_auth_user, #host_auth_password')
                    ->setGroupClass('mb-0'),

                (new GroupRow('row6'))->addElements(
                    (new InputText('host_auth_user', 'LBL_AUTH_USER'))
                        ->setColSize('col-6'),
                    (new inputPassword('host_auth_password', 'LBL_AUTH_PASSWORD'))
                        ->setColSize('col-6')
                        ->showTogglePassword()
                ),

                (new InputText('host_auth_realm', 'LBL_AUTH_REALM', 'Restricted area')),
                (new InputText('host_auth_error', 'LBL_AUTH_ERROR_MESSAGE', 'You are not authorized to see this page.'))
            );

        $this->addTabs($general, $smtp, $httpAuth);

        $this->addButtons(
            new buttonModalSave(),
            new buttonModalClose()
        );
	}

    public function onValidate(): void
    {
        if (!empty($this->values['host_host'])) {
            $res = $this->db->getFirstRow(
                "SELECT host_id FROM hosts WHERE host_host LIKE \"" . $this->db->escapeString($this->values['host_host']) . "\" AND host_id != '" . $this->getKeyValue('host_id') . "'"
            );
            if (!empty($res)) {
                $this->addError('ERR_10016', self::FORM_ERROR, ['host_host']);
            }
        }

        if(!Empty($this->values['host_languages'])) {
            if (!in_array($this->values['host_default_language'], $this->values['host_languages'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_default_language']);
            }
        }

        if(!Empty($this->values['host_currencies'])) {
            if(!in_array($this->values['host_default_currency'], $this->values['host_currencies'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_default_currency']);
            }
        }

        if(!Empty($this->values['host_protect'])){
            if(Empty($this->values['host_auth_user'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_user']);
            }
            if(Empty($this->values['host_auth_password'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_password']);
            }
            if(Empty($this->values['host_auth_realm'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_realm']);
            }
            if(Empty($this->values['host_auth_error'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['host_auth_error']);
            }
        }
    }

    public function onBeforeSave(): void
    {
        $this->values['host_client_id'] = $this->hostConfig->getClientId();
        $this->values['host_host'] = Utils::safeFileName($this->values['host_host']);
        $this->values['host_host'] = strtolower(trim($this->values['host_host']));

        if(Empty($this->values['host_force_ssl'])) $this->values['host_force_ssl'] = 0;
        if(Empty($this->values['host_production'])) $this->values['host_production'] = 0;
        if(Empty($this->values['host_share_session'])) $this->values['host_share_session'] = 0;
        if(Empty($this->values['host_protect'])) $this->values['host_protect'] = 0;
        if(Empty($this->values['host_maintenance'])) $this->values['host_maintenance'] = 0;
        if(Empty($this->values['host_sms_testmode'])) $this->values['host_sms_testmode'] = 0;

        if(Empty($this->values['host_smtp_port'])) $this->values['host_smtp_port'] = 0;
        if(Empty($this->values['host_smtp_ssl'])) $this->values['host_smtp_ssl'] = 'SSL';

        if(!Empty($this->values['host_smtp_pwd'])){
            $this->values['host_smtp_pwd'] = serialize(Str::cryptString(SECURE_SALT_KEY, $this->values['host_smtp_pwd']));
        }else{
            $this->values['host_smtp_pwd'] = '';
        }

        if(!Empty($this->values['host_sms_pwd'])){
            $this->values['host_sms_pwd'] = serialize(Str::cryptString(SECURE_SALT_KEY, $this->values['host_sms_pwd']));
        }else{
            $this->values['host_sms_pwd'] = '';
        }

        if(!Empty($this->values['host_auth_password'])){
            $this->values['host_auth_password'] = serialize(Str::cryptString(SECURE_SALT_KEY, $this->values['host_auth_password']));
        }else{
            $this->values['host_auth_password'] = '';
        }
    }

    public function onAfterSave($statement): void
    {
        MemcachedHandler::create()->delete(HOST_SETTINGS . $this->values['host_host']);
    }

    public function onAfterLoadValues(): void
    {
        if(!Empty($this->values['host_smtp_pwd'])){
            $pwd = unserialize($this->values['host_smtp_pwd']);
            if($pwd) {
                $this->values['host_smtp_pwd'] = Str::deCryptString(SECURE_SALT_KEY, $pwd);
            }
        }

        if(!Empty($this->values['host_sms_pwd'])){
            $pwd = unserialize($this->values['host_sms_pwd']);
            if($pwd) {
                $this->values['host_sms_pwd'] = Str::deCryptString(SECURE_SALT_KEY, $pwd);
            }
        }

        if(!Empty($this->values['host_auth_password'])){
            $pwd = unserialize($this->values['host_auth_password']);
            if($pwd) {
                $this->values['host_auth_password'] = Str::deCryptString(SECURE_SALT_KEY, $pwd);
            }
        }
    }

    public function onAfterInit(): void
    {
        if($this->getKeyValue('host_id')) {
            $this->setSubTitle($this->values['host_name'] . ' (' . $this->values['host_host'] . ')');
        }
    }
}
