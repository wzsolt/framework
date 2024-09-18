<?php
namespace Applications\Admin\Controllers\Tables;

use Applications\Admin\Controllers\Forms\EditTemplateForm;
use Framework\Components\HostConfig;
use Framework\Controllers\Tables\AbstractTable;
use Framework\Controllers\Tables\Column;
use Framework\Controllers\Tables\ColumnHidden;

class TemplatesTable extends AbstractTable
{

    protected function setupKeyFields(): void
    {
        $this->setKeyField('mt_id');
    }

	public function setup(): void
    {
        $this->setDatabase('templates');

        $this->addWhere('mt_client_id = ' . HostConfig::create()->getClientId());
        $this->setForm(new EditTemplateForm());

        $this->setOrderBy('mt_id', 'ASC', 50);
        $this->disableDelete();
        $this->setCounterHidden();

		$this->addColumns(
            (new Column('mt_type', 'LBL_TYPE', 1))
                ->addClass('text-center')
                ->setTemplate('<b>{{ val }}</b>'),
            (new Column('mt_key', 'LBL_TEMPLATE', 9))
                ->setTemplate('{{ _("LBL_TEMPLATE_" ~ val) }}{% if row.mt_description %}<br><i class="text-muted text-sm">{{ row.mt_description }}</i>{% endif %}'),
            new ColumnHidden('mt_description')
        );
	}

}
