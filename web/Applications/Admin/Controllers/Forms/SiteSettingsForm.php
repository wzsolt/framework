<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\ChangeAction;
use Framework\Components\Enums\Color;
use Framework\Components\Enums\MessageType;
use Framework\Components\Messages;
use Framework\Controllers\Buttons\ButtonCancel;
use Framework\Controllers\Buttons\ButtonSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Components\PreviewImage;
use Framework\Controllers\Forms\Containers\GroupHtml;
use Framework\Controllers\Forms\Containers\GroupRow;
use Framework\Controllers\Forms\Inputs\InputCheckbox;
use Framework\Controllers\Forms\Inputs\InputFile;
use Framework\Controllers\Forms\Inputs\InputSwitch;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Controllers\Forms\Inputs\InputTextarea;
use Framework\Controllers\Forms\Sections\SectionBox;
use Framework\Locale\Translate;
use Framework\Models\Database\Db;
use Framework\Models\Memcache\MemcachedHandler;
use Framework\Router;

class SiteSettingsForm extends AbstractForm
{
    const LOGO_TYPES = [
        'dark'          => 'logo-dark.svg',
        'darkSmall'     => 'logo-dark-small.svg',
        'light'         => 'logo-light.svg',
        'lightSmall'    => 'logo-light-small.svg',
        'mail'          => 'logo-mail.png',
        'favicon'       => 'favicon.png',
        'loginBg'       => 'login-bg.jpg',
    ];

    public function setupKeyFields(): void
    {
    }

    protected function setAccessLevel(): AccessLevel
    {
        return $this->user->getAccessLevel('site-settings');
    }

    public function setup():void
    {
        $mainOptions = (new SectionBox('main', 'LBL_GENERAL_SETTINGS', 'far fa-cogs'))
            ->addElements(
                (new InputText('siteName', 'LBL_SITE_NAME'))
                    ->setIcon('far fa-globe')
                    ->setName('settings/siteName')
            )
            ->addElements(
                (new GroupRow())->addElements(
                    (new InputSwitch('darkTheme', 'LBL_DARK_THEME'))
                        ->setColor(Color::Primary)
                        ->setName('settings/darkTheme'),
                    (new InputSwitch('darkSideBar', 'LBL_DARK_SIDEBAR'))
                        ->setColor(Color::Primary)
                        ->setName('settings/darkSideBar'),
                )
            );

        $contact = (new sectionBox('contact', 'LBL_CONTACT_DATA', 'far fa-envelope'))
            ->addElements(
                (new InputText('emailSenderName', 'LBL_SENDER_NAME'))
                    ->setPlaceholder('LBL_SENDER_NAME')
                    ->setIcon('far fa-address-card')
                    ->setName('settings/emailSenderName'),
                (new InputText('outgoingEmail', 'LBL_OUTGOING_EMAIL_ADDRESS'))
                    ->setPlaceholder('noreply@company.com')
                    ->setIcon('far fa-inbox-out')
                    ->setName('settings/outgoingEmail'),
                (new InputText('incomingEmail', 'LBL_INCOMING_EMAIL_ADDRESS'))
                    ->setPlaceholder('info@company.com')
                    ->setIcon('far fa-inbox-in')
                    ->setName('settings/incomingEmail'),
                (new inputTextarea('registrationRecipients', 'LBL_REGISTRATION_RECIPIENTS'))
                    ->setPlaceholder('LBL_REGISTRATION_RECIPIENTS_SAMPLE')
                    ->setIcon('far fa-inbox-in')
                    ->setName('settings/registrationRecipients'),
                (new InputText('address', 'LBL_ADDRESS'))
                    ->setIcon('far fa-map-marker-alt')
                    ->setName('settings/address'),
                (new InputText('phone', 'LBL_PHONE'))
                    ->setIcon('far fa-phone-alt')
                    ->setName('settings/phone')
            );

        $analytics = (new SectionBox('trackers', 'LBL_ANALYTICS', 'far fa-analytics'))
            ->addElements(
                (new InputText('googleAnalytics', 'LBL_GOOGLE_ANALYTICS_ID'))
                    ->setIcon('fab fa-google')
                    ->setName('settings/googleAnalytics'),
                (new InputText('googleMapsAPI', 'LBL_GOOGLE_MAPS_API_KEY'))
                    ->setIcon('far fa-map-marker-alt')
                    ->setName('settings/googleMapsAPI'),

                (new InputSwitch('captcha', 'LBL_GOOGLE_CAPTCHA', 0))
                    ->setColor(Color::Warning)
                    ->setGroupClass('mb-0')
                    ->changeState(0, ChangeAction::Readonly, '#googleSiteKey, #googleSecret')
                    ->changeState(1, ChangeAction::Editable, '#googleSiteKey, #googleSecret')
                    ->setName('settings/captcha'),
                (new InputText('googleSiteKey', 'LBL_GOOGLE_SITE_KEY'))
                    ->setIcon('far fa-key')
                    ->setName('settings/googleSiteKey'),
                (new InputText('googleSecret', 'LBL_GOOGLE_SECRET'))
                    ->setIcon('far fa-key')
                    ->setName('settings/googleSecret'),

                (new InputText('facebookAppId', 'LBL_FACEBOOK_APP_ID'))
                    ->setIcon('fab fa-facebook')
                    ->setName('settings/facebookAppId')
            );

        $social = (new SectionBox('social', 'LBL_SOCIAL_MEDIA', 'far fa-share-alt'))
            ->addElements(
                (new InputText('facebook', 'LBL_FACEBOOK_PAGE'))
                    ->setIcon('fab fa-facebook')
                    ->setName('settings/facebook'),
                (new InputText('twitter', 'LBL_TWITTER_PAGE'))
                    ->setIcon('fab fa-twitter')
                    ->setName('settings/twitter'),
                (new InputText('pinterest', 'LBL_PINTEREST_PAGE'))
                    ->setIcon('fab fa-pinterest')
                    ->setName('settings/pinterest'),
                (new InputText('instagram', 'LBL_INSTAGRAM_PAGE'))
                    ->setIcon('fab fa-instagram')
                    ->setName('settings/instagram'),
                (new InputText('youtube', 'LBL_YOUTUBE_PAGE'))
                    ->setIcon('fab fa-youtube')
                    ->setName('settings/youtube')
            );

        $logo = new SectionBox('logo', 'LBL_LOGOS_AND_IMAGES', 'far fa-sign');

        $i = 0;
        foreach(self::LOGO_TYPES AS $type => $fileName){
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            $logo->addElements(
                (new InputFile('logo/' . $type, 'LBL_LOGO_' . strtoupper($type)))
                    ->setHelpText(Translate::create()->get('LBL_ALLOWED_FILETYPES', $ext))
                    ->addData('max-file-size', 10240)
                    ->addData('theme', 'fas')
                    ->addData('show-upload', 'false')
                    ->addData('show-caption', 'true')
                    ->addData('show-remove', 'false')
                    ->addData('show-cancel', 'false')
                    ->addData('show-close', 'false')
                    ->addData('allowed-file-extensions', '["' . $ext . '"]')
                    ->addData('show-preview', 'false')
                    ->notDBField(),
                (new PreviewImage('img_logo_' . $type))
                    ->setGroupClass((in_array($type, ['light', 'light-small']) ? 'bg-dark' : ''))
                    ->setSize(200)
                    ->setResponsive(true)
                    ->setPath(FOLDER_UPLOAD . $this->hostConfig->getClientId() . '/'),
                (new InputCheckbox('remove_' . $type, 'LBL_REMOVE_IMAGE', 0))
                    ->notDBField(),
                new GroupHtml('div-' . $i++, '<hr>')
            );
        }

        $this->addSections($mainOptions, $contact, $logo, $analytics, $social);

        $this->hideSidebar();

        $this->addButtons(
            new ButtonSave(),
            new ButtonCancel()
        );
	}

    public function onValidate(): void
    {
    }

    public function saveValues(): void
    {
        $this->uploadFiles();

        if(Empty($this->values['settings']['darkTheme'])) $this->values['settings']['darkTheme'] = 0;
        if(Empty($this->values['settings']['darkSideBar'])) $this->values['settings']['darkSideBar'] = 0;
        if(Empty($this->values['settings']['disableParsers'])) $this->values['settings']['disableParsers'] = 0;

        $this->values['settings']['logo'] = [];
        foreach(self::LOGO_TYPES AS $type => $fileName){
            if(file_exists(DIR_UPLOAD . $this->hostConfig->getClientId() . '/' . $fileName)){
                $this->values['settings']['logo'][$type] = true;
            }
        }

        $this->db->sqlQuery(
            Db::insert(
                'settings',
                [
                    'ws_client_id' => $this->hostConfig->getClientId(),
                    'ws_settings' => json_encode($this->values['settings'])
                ],
                [
                    'ws_client_id'
                ]
            )
        );

        MemcachedHandler::create()->delete(CACHE_SETTINGS . $this->hostConfig->getClientId());

        Messages::create()->add(MessageType::Success, 'LBL_DATA_SAVED_SUCCESSFULLY');

        Router::pageRedirect('/settings/system/site-settings/');
    }

    public function onAfterInit(): void
    {
        $row = $this->db->getFirstRow(
            Db::select(
                'settings',
                [
                    'ws_settings'
                ],
                [
                    'ws_client_id' => $this->hostConfig->getClientId(),
                ]
            )
        );
        if($row){
            $this->values['settings'] = json_decode($row['ws_settings'], true);
        }

        $path = DIR_UPLOAD . $this->hostConfig->getClientId() . '/';
        foreach(self::LOGO_TYPES AS $type => $fileName){
            if(file_exists($path . $fileName)){
                $this->values['img_logo_' . $type] = $fileName;
            }
        }
   }

    private function uploadFiles():void
    {
        $savePath = DIR_UPLOAD . $this->hostConfig->getClientId() . '/';
        if(!is_dir($savePath)){
            @mkdir($savePath, 0777, true);
            @chmod($savePath, 0777);
        }

        foreach(self::LOGO_TYPES AS $type => $fileName){
            if(!Empty($this->values['remove_' . $type])) {
                if (file_exists($savePath . $fileName)) {
                    @unlink($savePath . $fileName);
                }
            }
            unset($this->values['remove_' . $type]);
            unset($this->values['img_logo_' . $type]);

            if (!empty($_FILES[$this->name]['name']['logo'][$type]) && empty($_FILES[$this->name]['error']['logo'][$type])) {
                move_uploaded_file($_FILES[$this->name]['tmp_name']['logo'][$type], $savePath . $fileName);
            }
        }
    }
}
