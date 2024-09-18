<?php

namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Applications\Admin\Controllers\Forms\MyProfileForm;
use Framework\Components\HostConfig;

class MyProfile extends AbstractPageConfig
{
    public function setup(): ?array
    {

        $this->addCss('fileinput.min.css', false, 'fileinput');
        $this->addCss('bootstrap-fileinput/css/fileinput.min.css', false, 'bootstrap-fileinput');

        $this->addJs('fileinput/fileinput.min.js', false, 'fileinput');
        $this->addJs('fileinput/fileinput_locale_' . HostConfig::create()->getLanguage() . '.js', false, 'fileinput-locale');
        $this->addJs("bootstrap-fileinput/js/fileinput.min.js", false, 'bootstrap-fileinput');
        $this->addJs("bootstrap-fileinput/themes/fas/theme.min.js", false, 'fileinput-theme');

        $this->setVariable('tab', ($_REQUEST['tab'] ?? ''));

        $myProfile = new MyProfileForm();

        $this->addForm($myProfile);

        return null;
    }
}