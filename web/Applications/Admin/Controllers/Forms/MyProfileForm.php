<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\MessageType;
use Framework\Components\Enums\Size;
use Framework\Components\Lists\ListCountries;
use Framework\Components\Lists\ListTimeZones;
use Framework\Components\Lists\ListTitles;
use Framework\Components\Messages;
use Framework\Controllers\Buttons\ButtonCancel;
use Framework\Controllers\Buttons\ButtonSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Containers\GroupCol;
use Framework\Controllers\Forms\Containers\GroupFieldset;
use Framework\Controllers\Forms\Containers\GroupInclude;
use Framework\Controllers\Forms\Containers\GroupRow;
use Framework\Controllers\Forms\Inputs\InputSelect;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Controllers\Forms\Sections\SectionTab;
use Framework\Router;

class MyProfileForm extends AbstractForm
{
    public function setupKeyFields(): void
    {
        $this->setKeyField('us_id', $this->user->getId());
    }

    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }

    public function setup(): void
    {
        $this->setDatabaseTable('users', 'us_');

		$this->displayErrors = true;
        $this->boxed = false;

        $this->addExtraField('us_hash');
        $this->addExtraField('us_img');

        $userIDs = (new GroupFieldset('ids-data', 'LBL_USER_IDS', 'mb-3'))->addElements(
            (new GroupRow())->addElements(
                (new GroupCol(false, 'col-6'))->addElements(
                    (new InputText('us_code', 'LBL_CODE'))
                        ->setColSize('col-12 col-lg-3')
                        ->addClass('text-center text-primary fw-bolder')
                        ->setInputSize(Size::Lg)
                        ->setReadonly(),
                )
            )
        );

        $general = (new GroupFieldset('general-data', 'LBL_GENERAL', 'mb-3'))->addElements(
            (new GroupRow())->addElements(
                (new GroupCol(false, 'col-12 col-lg-6'))->addElements(
                    (new InputSelect('us_title', 'LBL_PERSON_TITLE'))
                        ->setColSize('col-6')
                        ->setOptions((new ListTitles())->getList()),
                    (new InputText('us_lastname', 'LBL_LASTNAME'))
                        ->setRequired()
                        ->setColSize('col-12'),
                    (new InputText('us_firstname', 'LBL_FIRSTNAME'))
                        ->setRequired()
                        ->setColSize('col-12'),
                    (new InputText('us_email', 'LBL_EMAIL'))
                        ->setColSize('col-12')
                        ->setIcon('fas fa-envelope')
                        ->setRequired(),
                    (new InputText('us_phone', 'LBL_PHONE'))
                        ->setColSize('col-12')
                        ->setIcon('fas fa-phone')
                        ->setRequired()
                        ->onlyNumbers('+')
                ),
                (new GroupCol(false, 'col-12 col-lg-6 ms-auto'))->addElements(
                    (new GroupInclude('user-profile-img', [
                        'upload' => true,
                        'src' => false
                    ]))
                )
            )
        );

        $address = (new groupFieldset('address-data', 'LBL_ADDRESS', 'mb-3'))->addElements(
            (new groupRow())->addElements(
                (new inputSelect('us_country', 'LBL_COUNTRY', 'HU'))
                    ->setOptions((new ListCountries())->getList())
                    ->setColSize('col-3'),
                (new inputText('us_zip', 'LBL_ZIP'))
                    ->onlyNumbers()
                    ->setColSize('col-2'),
                (new inputText('us_city', 'LBL_CITY'))
                    ->setColSize('col-7'),
                (new inputText('us_address', 'LBL_ADDRESS'))
                    ->setColSize('col-12')
            )
        );

        $settings = (new groupFieldset('settings-data', 'LBL_SETTINGS', 'mb-3'))->addElements(
            (new groupRow())->addElements(
                (new inputSelect('us_timezone', 'LBL_TIMEZONE', ($this->owner->hostConfig['timeZoneId'] ?? 0)))
                    ->setOptions((new ListTimeZones())->getList())
                    ->setColSize('col-6')
            )
        );

        $sectionGeneral = (new SectionTab('user', 'LBL_USER_DATA', 'fal fa-id-card', true))->addElements(
            $userIDs,
            $general,
            $address,
            $settings
        );

        $this->addTabs(
            $sectionGeneral
        );

        $sectionPassword = (new SectionTab('security', 'LBL_CHANGE_PASSWORD', 'fal fa-lock'))
            ->setLink('/ajax/forms/ChangePasswordForm/')
            ->addData('bs-toggle', 'modal')
            ->addData('bs-target', '#ajax-modal')
            ->addClass('bg-danger text-white');

        $this->addTabs(
            $sectionPassword
        );

        $this->addButtons(
            new ButtonSave()
        );
	}

	public function onAfterLoadValues(): void
    {
        if(Empty($this->values['us_img'])){
            $imgSrc = '/images/' . strtolower(($this->values['us_title'] ?? 'MR')) . '.svg';
        }else{
            $imgSrc = FOLDER_UPLOAD . $this->hostConfig->getClientId() . '/profiles/' . $this->user->getId() . '/' . $this->values['us_img'];
        }

        $this->getControl('user-profile-img')->setData([
            'fileName' => $this->values['us_img'],
            'src' => $imgSrc
        ]);
	}

    public function onAfterSave($statement): void
    {
        Messages::create()->add(MessageType::Success, 'LBL_DATA_SAVED_SUCCESSFULLY');

        Router::pageRedirect('/my-profile/');
    }

}
