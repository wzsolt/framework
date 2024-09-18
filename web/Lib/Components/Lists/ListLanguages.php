<?php

namespace Framework\Components\Lists;

use Framework\Components\HostConfig;

class ListLanguages extends AbstractList
{
    private bool $selectableOnly = false;

    public function setOptions(bool $selectableOnly):self
    {
        $this->selectableOnly = $selectableOnly;

        return $this;
    }

    protected function setup(): array
    {
        $list = [];

        if($this->selectableOnly) {
            $languages = HostConfig::create()->getLanguages();

            if (!empty($languages)) {
                foreach ($languages as $lang) {
                    $list[$lang] = $GLOBALS['REGIONAL_SETTINGS'][$lang]['name'];
                }
            }
        }else {
            foreach ($GLOBALS['REGIONAL_SETTINGS'] as $lang => $setting) {
                if ($lang != 'default') {
                    $list[$lang] = $setting['name'];
                }
            }
        }

        return $list;
    }
}