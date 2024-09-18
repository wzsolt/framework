<?php
namespace Framework\Controllers\Forms;

trait ElementMaskTrait
{

    public function setCustomMask(string $mask):self
    {
        $this->addClass('inputmask');

        $this->addData('inputmask', "'mask': '" . $mask . "'");

        return $this;
    }

    public function setMaskAlias(string $alias):self
    {
        $this->addClass('inputmask');

        $this->addData('inputmask-alias', $alias);

        return $this;
    }

    public function setMaskDecimal(int $digits = 2, string $separator = ','):self
    {
        $this->setMaskAlias('decimal');

        $this->addData('inputmask-digits', $digits);

        $this->addData('inputmask-separator', $separator);

        return $this;
    }

    public function setMaskInteger(int $min = 0, int $max = 0):self
    {
        $this->setMaskAlias('integer');

        if($min){
            $this->addData('inputmask-min', $min);
        }

        if($max){
            $this->addData('inputmask-max', $max);
        }

        return $this;
    }

    public function setMaskPercent():self
    {
        $this->setMaskAlias('percentage');

        return $this;
    }

    public function setMaskCurrency():self
    {
        $this->setMaskAlias('currency');

        return $this;
    }

    public function setMaskDateTime(string $format, bool $showPlaceholder = true):self
    {
        $this->setMaskAlias('datetime');
        $this->addData('inputmask-inputformat', $format);

        if($showPlaceholder) {
            $this->setPlaceholder($format);
        }

        return $this;
    }

    public function setMaskUrl():self
    {
        $this->setMaskAlias('url');

        return $this;
    }
}