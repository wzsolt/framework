<?php
namespace Applications\Admin\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Controllers\Buttons\ButtonModalClose;
use Framework\Controllers\Buttons\ButtonModalSave;
use Framework\Controllers\Forms\AbstractForm;
use Framework\Controllers\Forms\Inputs\InputText;
use Framework\Helpers\Utils;
use Framework\Models\Database\Db;

class AddFunctionForm extends AbstractForm
{
    const FN_GENERAL = 'fnGeneral';

    public function setupKeyFields(): void
    {
        $this->setForeignKeyField('key');
        $this->setForeignKeyField('app');
        $this->setForeignKeyField('group');
    }

    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }

    public function setup():void
    {
        $this->setTitle('LBL_ADD_FUNCTION');

        $this->reloadPage = true;

        $this->addControls(
            (new InputText('af_name', 'LBL_FUNCTION_NAME'))
                ->setRequired(),
            (new InputText('af_key', 'LBL_FUNCTION_KEY'))
                ->setRequired()
                ->setPrepend(($this->getForeignKeyValue('key') == self::FN_GENERAL ? '' : $this->getForeignKeyValue('key') . '-'))
        );

        $this->addButtons(
            new ButtonModalSave(),
            new ButtonModalClose()
        );
	}

    public function saveValues(): void
    {
        $this->values['af_app'] = ucfirst($this->getForeignKeyValue('app'));

        $this->values['af_group'] = $this->getForeignKeyValue('group');

        $this->values['af_page'] = $this->getForeignKeyValue('key');

        if($this->getForeignKeyValue('key') == self::FN_GENERAL){
            $this->values['af_key'] = Utils::safeURL($this->values['af_key']);
        }else {
            $this->values['af_key'] = $this->getForeignKeyValue('key') . '-' . Utils::safeURL($this->values['af_key']);
        }

        $this->db->sqlQuery(
            Db::insert(
                'access_functions',
                $this->values
            )
        );
    }
}
