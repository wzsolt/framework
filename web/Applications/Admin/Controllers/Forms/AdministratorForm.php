<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Components\Enums\Size;
use Framework\Components\Lists\ListTimeZones;
use Framework\Components\Lists\ListTitles;
use Framework\Components\Lists\ListUserRoles;
use Framework\Components\Uuid;
use Framework\Controllers\Buttons\ButtonModalClose;
use Framework\Controllers\Buttons\ButtonModalSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Containers\GroupCol;
use Framework\Controllers\Forms\Containers\GroupFieldset;
use Framework\Controllers\Forms\Containers\GroupRow;
use Framework\Controllers\Forms\Inputs\InputSelect;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Controllers\Forms\Sections\SectionTab;
use Framework\Router;

class AdministratorForm extends AbstractForm
{

    public function setupKeyFields(): void
    {
        $this->setKeyField('us_id');
    }

    protected function setAccessLevel(): AccessLevel
    {
        return $this->user->getAccessLevel('administrators');
    }

    public function setup(): void
    {
        $this->setDatabaseTable('users');

		$this->setTitle('LBL_EDIT_ADMINISTRATOR');

        $mainTab = new SectionTab('general', 'LBL_GENERAL', '', true);
        $userIDs = (new GroupFieldset('ids-data'))->addElements(
            (new GroupRow())->addElements(
                (new GroupCol(false, 'col-6'))->addElements(
                    (new InputText('us_code', 'LBL_CODE'))
                        ->setColSize('col-12 col-lg-6')
                        ->setRequired()
                        ->setMaxLength(3)
                        ->addClass('text-center text-primary fw-bolder')
                        ->setInputSize(Size::Lg),
                )
            )
        );
        $mainTab->addElements($userIDs);

        $general = (new GroupFieldset('general-data'))->addElements(
            (new GroupRow('row1'))->addElements(
                (new InputSelect('us_title', 'LBL_PERSON_TITLE'))->setColSize('col-2')->setOptions((new ListTitles())->getList()),
                (new InputText('us_lastname', 'LBL_LASTNAME'))->setRequired()->setColSize('col-5'),
                (new InputText('us_firstname', 'LBL_FIRSTNAME'))->setRequired()->setColSize('col-5')
            ),
            (new inputText('us_email', 'LBL_EMAIL'))->setRequired(),
            (new inputText('us_phone', 'LBL_PHONE'))
        );
        $mainTab->addElements($general);

        $settings = (new GroupFieldset('settings-data', 'LBL_SETTINGS', 'mb-3'))->addElements(
            (new GroupRow())->addElements(
                (new InputSelect('us_timezone', 'LBL_TIMEZONE', $this->hostConfig->getTimeZoneCode()))
                    ->setOptions((new ListTimeZones())->getList())
                    ->setColSize('col-12')
            )
        );
        $mainTab->addElements($settings);

        $role = (new GroupFieldset('user-role'))->addElements(
            (new InputSelect('us_role', 'LBL_ROLE', 'USER'))
        );

        $this->addTabs(
            $mainTab,
            (new SectionTab('roles', 'LBL_USER_ROLES'))->addElements($role)
        );

        $this->addButtons(
            new ButtonModalSave(),
            new ButtonModalClose()
        );
	}

	public function onAfterLoadValues(): void
    {
		$editorRole = $this->user->getRole();
		$editorRoleLevel = $this->getRoleLevel($editorRole);

        $list = New ListUserRoles(['group' => USER_GROUP_ADMINISTRATORS]);

		if(Empty($this->getKeyValue('us_id'))){
			$options = $list->setParams(['highestRole' => $editorRole])->getList();

		}else{
			$userRoleLevel = $this->getRoleLevel($this->values['us_role']);
			if($userRoleLevel < $editorRoleLevel){
                $options = $list->setParams(['highestRole' => false])->getList();

                $this->changeControlProperty('us_role', 'setReadonly', true);
			}else{

                $options = $list->setParams(['highestRole' => $editorRole])->getList();
			}
        }

        $this->changeControlProperty('us_role', 'setOptions', $options);
    }

	public function onLoadValues($isFound = false): void
    {
		if(!$this->values && !Empty($this->getKeyValue('us_id'))){
			Router::pageRedirect('/settings/system/administrators/');
		}
	}

	public function onValidate(): void
    {
		if (!empty($this->values['us_email'])) {
			$res = $this->db->getFirstRow(
				"SELECT us_id FROM users WHERE us_client_id = " . $this->hostConfig->getClientId() . " AND us_email LIKE \"" . $this->db->escapeString($this->values['us_email']) . "\" AND us_id != '" . $this->getKeyValue('us_id') . "'"
			);
			if (!empty($res)) {
				$this->addError('ERR_EMAIL_IS_REGISTERED', self::FORM_ERROR, ['us_email']);
			}
		}
        if (!empty($this->values['us_code'])) {
            $res = $this->db->getFirstRow(
                "SELECT us_id FROM users WHERE us_client_id = " . $this->hostConfig->getClientId() . " AND us_code = \"" . $this->db->escapeString($this->values['us_code']) . "\" AND us_id != '" . $this->getKeyValue('us_id') . "'"
            );
            if (!empty($res)) {
                $this->addError('ERR_USER_CODE_IS_RESERVED', self::FORM_ERROR, ['us_code']);
            }
        }
	}

	public function onBeforeSave(): void
    {
		if(!$this->getKeyValue('us_id')){
			$this->values['us_password'] = password_hash($this->values['us_email'] . microtime(true), PASSWORD_DEFAULT);
			$this->values['us_hash'] = Uuid::v4();
		}

        $this->values['us_code'] = strtoupper($this->values['us_code']);
		$this->values['us_group'] = USER_GROUP_ADMINISTRATORS;
		$this->values['us_client_id'] = $this->hostConfig->getClientId();
        $this->values['us_ug_id'] = 1;
	}

	public function onAfterSave(string $statement): void
    {
        $this->user->clearUserDataCache($this->getKeyValue('us_id'));
    }

    public function onAfterInit(): void
    {
        $this->addInlineJs("
			$('#us_role').trigger('change');
        ");
    }

    private function getRoleLevel(string $role):int
    {
		$roleLevel = 0;
		$level = 0;

        $roles = New ListUserRoles(['group' => USER_GROUP_ADMINISTRATORS]);

        foreach($roles->getList() AS $key => $value){
			if($role == $key){
				$roleLevel = $level;
				break;
			}
			$level++;
		}

		return $roleLevel;
	}
}
