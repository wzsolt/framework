<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputDate extends AbstractFormElement
{
    const Type = 'text';

    private string $placeholder = '';

    public function getType():string
    {
        return $this::Type;
    }

    public function getTemplate():string
    {
        return 'text';
    }

    public function onlyNumbers(string $chars = ''):self
    {
        $this->addClass('numbersonly');

        if(!Empty($chars)){
            $this->addData('chars', $chars);
        }

        return $this;
    }

    public function setPlaceholder(string $placeholder):self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder():string
    {
        return $this->placeholder;
    }

    public function setNumberOfCalendars(int $calendars):self
    {
        $this->addData('calendars', $calendars);

        return $this;
    }

    public function setMaxDate($maxDate):self
    {
        /**
         * @todo formatted date
         * $this->owner->lib->formatDate( date('Y-m-d') ),
         */
        $this->addData('max-date', $maxDate);

        return $this;
    }

    public function setMinDate($minDate):self
    {
        /**
         * @todo formatted date
         * $this->owner->lib->formatDate( date('Y-m-d') ),
         */
        $this->addData('min-date', $minDate);

        return $this;
    }

    public function setYearRange(int $from, int $to):self
    {
        $this->addData('year-range', $from . ':' . $to);

        return $this;
    }

    public function limitRangeFrom(string $formId):self
    {
        $this->addData('range-from', $formId);

        return $this;
    }

    public function limitRangeTo(string $formId):self
    {
        $this->addData('range-to', $formId);

        return $this;
    }

    protected function init():void
    {
        if(!$this->readonly || !$this->disabled) {
            $this->addClass('datepicker');
            $this->addData('calendars', 1);
            $this->addData('change-month', 'true');
            $this->addData('change-year', 'true');
            $this->addData('dateformat', $this->locals['dateformat']);
            $this->addData('language', $this->language);
        }

        /**
         * @todo format default value
         */
        //$this->default = $this->owner->lib->formatDate($this->default);
    }
}