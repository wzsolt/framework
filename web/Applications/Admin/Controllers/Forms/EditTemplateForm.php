<?php

namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Controllers\Buttons\ButtonCancel;
use Framework\Controllers\Buttons\ButtonSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Containers\GroupHtml;
use Framework\Controllers\Forms\Inputs\InputEditor;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Locale\Translate;

class EditTemplateForm extends AbstractForm
{
    public function setupKeyFields(): void
    {
        $this->setKeyField('mt_id');
    }

	public function setup(): void
    {
        $this->setDatabaseTable('templates');

        $this->setTitle('LBL_EDIT_TEMPLATE');

        $this->addExtraField('mt_keywords');
        $this->addExtraField('mt_description');

        $this->boxed = false;

        $this->addControls(
            (new InputText('mt_subject', 'LBL_TITLE'))
                ->setRequired(),

            (new InputEditor('mt_body', 'LBL_BODY'))
                ->setHeight(200)
                ->setRequired()
        );

        $this->addButtons(
            new ButtonSave(),
            new ButtonCancel()
        );
	}

	public function onAfterLoadValues(): void
    {
		if(isset($this->values['mt_keywords'])){
			$keywords = explode('|', $this->values['mt_keywords']);
            $html  = '<div class="col-12">' . Translate::create()->get('LBL_AVAILABLE_VARIABLES') . ':<br>';
            foreach($keywords AS $key){
                $html .= '<span class="badge bg-info ms-1 btn-insert-text cursor-pointer">{{ "{{" }} ' . $key . ' {{ "}}" }}</span>';
            }
            $html .= '</div>';

            $this->addControls(
                new GroupHtml('tags', $html)
            );
		}
	}

	public function onAfterInit(): void
    {
        if($this->getKeyValue('mt_id')) {
            $this->setSubtitle($this->values['mt_description']);
        }
	}

	public function onBeforeSave(): void
    {
        $this->values['mt_client_id'] = $this->hostConfig->getClientId();
		unset($this->values['mt_keywords']);
	}

    protected function setAccessLevel(): AccessLevel
    {
        return $this->user->getAccessLevel('templates');
    }
}
