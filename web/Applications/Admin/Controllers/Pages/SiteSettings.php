<?php

namespace Applications\Admin\Controllers\Pages;

use Applications\Admin\Controllers\AbstractPageConfig;
use Applications\Admin\Controllers\Forms\SiteSettingsForm;
use Framework\Components\HostConfig;

class SiteSettings extends AbstractPageConfig
{
    public function setup(): ?array
    {
        $siteSettings = new SiteSettingsForm();

        $this->addForm($siteSettings);

        return null;
    }
}