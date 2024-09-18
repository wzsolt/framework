<?php

namespace Framework\Components\Lists;

use Framework\Components\HostConfig;

class ListCurrencies extends AbstractList
{
    private bool $selectableOnly = false;

    private bool $withCurrencySign = false;

    public function setOptions(bool $selectableOnly, bool $withCurrencySign = false):self
    {
        $this->selectableOnly = $selectableOnly;

        $this->withCurrencySign = $withCurrencySign;

        return $this;
    }

    protected function setup(): array
    {
        $list = [];

        if($this->selectableOnly) {
            $currencies = HostConfig::create()->getCurrencies();

            if (!empty($currencies)) {
                foreach ($currencies as $currency) {
                    $list[$currency] = ($this->withCurrencySign ? $GLOBALS['CURRENCIES'][$currency]['sign'] : $currency);
                }
            }
        }else{
            foreach ($GLOBALS['CURRENCIES'] as $key => $currency) {
                $list[$key] = ($this->withCurrencySign ? $currency['sign'] : $key);
            }
        }

        return $list;
    }

}