<?php
namespace Applications\Admin\Controllers\Tables;

use Applications\Admin\Controllers\Forms\HostForm;
use Framework\Components\Enums\Size;
use Framework\Components\HostConfig;
use Framework\Controllers\Buttons\TableButtonNewRecord;
use Framework\Controllers\Tables\AbstractTable;
use Framework\Controllers\Tables\Column;
use Framework\Helpers\Utils;
use Framework\Models\Database\Db;
use Framework\Models\Memcache\MemcachedHandler;

class HostsTable extends AbstractTable
{
    protected function setupKeyFields(): void
    {
        $this->setKeyField('host_id');
    }

    public function setup():void
    {
        $this->setDatabase('hosts');

        $this->addWhere('host_client_id = ' . HostConfig::create()->getClientId());

        $this->setForm(new HostForm(), true)->setModalSize(Size::Lg);

        $this->setOrderBy('host_name', 'ASC', 50);

        $this->allowCopy(
            [],
            [],
            [
                'host_name' => ' (copy)',
                'host_host' => '.' . Utils::generateRandomString(3),
            ]
        );

        $this->addColumns(
            (new Column('host_name', 'LBL_HOST_SITE_NAME', 4)),
            (new Column('host_host', 'LBL_HOST_NAME', 4)),
            (new Column('host_application', 'LBL_APPLICATION', 2))
        );

        $btnNew = new TableButtonNewRecord('BTN_NEW_HOST');
        $btnNew->setModalSize(Size::Lg);

        $this->addButton($btnNew);
	}

    public function onBeforeDelete(bool $real = true): bool
    {
        $row = Db::create()->getFirstRow(
            Db::select(
                'hosts',
                [
                    'host_host'
                ],
                [
                    'host_id' => $this->getKeyValues()['host_id']
                ]
            )
        );
        if($row){
            MemcachedHandler::create()->get(HOST_SETTINGS . $row['host_host']);
        }

        return true;
    }

}
