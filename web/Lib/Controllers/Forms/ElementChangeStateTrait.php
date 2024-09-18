<?php
namespace Framework\Controllers\Forms;

use Framework\Components\Enums\ChangeAction;

trait ElementChangeStateTrait
{
    private array $states = [];

    public function changeDefaultState(ChangeAction $action, string $elementIds):self
    {
        $this->addData('state-default', json_encode([$action->name => $elementIds]));

        return $this;
    }

    public function changeState(string $onValue, ChangeAction $action, string $elementIds):self
    {
        $this->addClass('change-state');
        $this->states[$onValue][$action->name] = $elementIds;
        $this->buildData();

        return $this;
    }

    private function buildData():void
    {
        $this->addData('state-options', json_encode($this->states));
    }
}