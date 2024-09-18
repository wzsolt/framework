<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputRecaptcha extends AbstractFormElement
{
    const Type = 'hidden';

    private string $siteKey;

    private string $action;

    public function __construct(string $token, string $siteKey, string $action)
    {
        $this->siteKey = $siteKey;

        $this->action = $action;

        parent::__construct($token);
    }

    public function getType():string
    {
        return $this::Type;
    }

    protected function init():void
    {
        $this->addJs('https://www.google.com/recaptcha/api.js?render=' . $this->siteKey, false, 'recaptcha');

        $this->addInlineJs(
            "grecaptcha.ready(function() {
                    grecaptcha.execute('" . $this->siteKey . "', {action: '" . $this->action . "'}).then(function(token) {
                        $('#" . $this->getId() . "').val(token);									
                    });
                });"
        );
    }
}