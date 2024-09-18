<?php
namespace Applications\Admin\Controllers\Tables;

use Applications\Admin\Controllers\Forms\AdministratorForm;
use Framework\Components\Email;
use Framework\Components\Enums\MessageType;
use Framework\Components\Enums\Size;
use Framework\Components\HostConfig;
use Framework\Components\Lists\ListUserRoles;
use Framework\Components\Messages;
use Framework\Controllers\Buttons\TableButtonNewRecord;
use Framework\Controllers\Tables\AbstractTable;
use Framework\Controllers\Tables\Column;
use Framework\Controllers\Tables\ColumnHidden;
use Framework\Controllers\Tables\ColumnSwitch;
use Framework\Models\Database\Db;
use Framework\Router;

class AdministratorsTable extends AbstractTable
{
	protected function setupKeyFields(): void
    {
        $this->setKeyField('us_id');
    }

    protected function setup():void
    {
        $this->setDatabase('users');

        $this->addWhere('us_group = "' . USER_GROUP_ADMINISTRATORS . '"');

        $this->setForm(new AdministratorForm(), true)->setModalSize(Size::Lg);

        $this->setArchivable('us_deleted');

        $this->setRowOptions(true, 2, '', 'table-options-user');

        $this->setOrderBy('us_lastname, us_firstname', 'ASC', 15);

        $this->addColumns(
            (new ColumnSwitch('us_enabled', 'LBL_ENABLED_SHORT', 1))
                ->addClass('text-center'),
            (new Column('us_code', 'LBL_NAME', 5))
                ->setTemplate('{{ _name(val, row.us_firstname, row.us_lastname) }}'),
            (new Column('us_role', 'LBL_ROLE', 2))
                ->addClass('text-center')
                ->setTemplate('{{ userRole(val)|raw }}'),
            (new Column('us_last_login', 'LBL_LAST_LOGIN', 2))
                ->addClass('text-center')
                ->setTemplate('{{ _date(val, 5) }}'),

            new ColumnHidden('us_firstname'),
            new ColumnHidden('us_lastname')
        );

        $btnNew = new TableButtonNewRecord('BTN_NEW_ADMINISTRATOR');
        $btnNew->setModalSize(Size::Lg);

        $this->addButton($btnNew);
	}

	protected function onAfterDelete(bool $real = true): void
    {
        $email = $this->db->getFirstRow(
            Db::select(
                'users',
                [
                    'us_id',
                    'us_email'
                ],
                [
                    'us_id' => $this->getKeyValue('us_id'),
                    'us_client_id' => HostConfig::create()->getClientId(),
                ]
            )
        );
		if($email) {
			$this->db->sqlQuery(
				Db::update(
					'users',
					[
						'us_enabled' => 0,
						'us_code' => $this->getKeyValue('us_id'),
						'us_email' => 'xxx|' . $email['us_email'] . '|xxx'
					],
					[
						'us_id' => $this->getKeyValue('us_id')
					]
				)
			);
		}
	}

	public function sendPassword():void
    {
        $user = $this->db->getFirstRow(
            Db::select(
                'users',
                [
                    'us_password_sent',
                    'us_email'
                ],
                [
                    'us_id' => $this->getKeyValue('us_id')
                ],
                [],
                false,
                false,
                1
            )
        );
        if($user){
            $counter = (int) $user['us_password_sent'];
			if($counter > 0){
				$template = Email::MAIL_REQUEST_NEW_PASSWORD;;
			}else{
				$template = Email::MAIL_NEW_PASSWORD;
			}

			$data = [
				'id' => $this->getKeyValue('us_id'),
				'email' => $user['us_email'],
				'link' => $this->user->getPasswordChangeLink($this->getKeyValue('us_id'), false, false, 24*7)
			];

            $sent = Email::create()->setTemplate($template, $data)->addUser($this->getKeyValue('us_id'))->send();

			if($sent == 1){
                Messages::create()->add(MessageType::Success, 'LBL_PASSWORD_SENT_SUCCESSFULLY');
			}else{
                Messages::create()->add(MessageType::Error, 'LBL_PASSWORD_WAS_NOT_SENT');
			}

			$this->db->sqlQuery(
				Db::update(
					'users',
					[
						'us_password_sent' => 'INCREMENT',
					],
					[
						'us_id' => $this->getKeyValue('us_id')
					]
				)
			);

			Router::pageRedirect('/settings/system/administrators/');
		}
	}

	public function onAfterLoad(): void
    {
		if($this->getRows()){
			foreach($this->getRows() AS $key => $row){
                $value['options']['delete'] = $this->hasHigherRole($row['us_id'], $row['us_role']);

                $this->changeRow($key, $value);
			}
		}
	}

	public function isDeletable(): bool
    {
        $user = $this->db->getFirstRow(
            Db::select(
                'users',
                [
                    'us_id',
                    'us_role',
                ],
                [
                    'us_client_id' => HostConfig::create()->getClientId(),
                    'us_id' => $this->getKeyValue('us_id'),
                ],
                [],
                false,
                false,
                1
            )
        );

        if($user){
            return $this->hasHigherRole($user['us_id'], $user['us_role']);
        }

		return true;
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

	private function hasHigherRole(int $userId, string $userRole):bool
    {
		if($userId != $this->user->getId()){
			$editorRoleLevel = $this->getRoleLevel($this->user->getRole());
			$userRoleLevel = $this->getRoleLevel($userRole);
			return ($editorRoleLevel <= $userRoleLevel);
		}else {
			return false;
		}
	}
}
