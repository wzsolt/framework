<?php
namespace Framework\Controllers\Forms;

use Framework\Components\Enums\AccessLevel;
use Framework\Controllers\Buttons\ButtonSubmit;
use Framework\Models\Database\Db;
use Framework\Models\Session\Session;
use Framework\Router;

abstract class AbstractFilterForm extends AbstractForm
{
	protected string|false $parentTable = false;

	public bool $hasFilter = false;

    abstract protected function setupFilterForm();

    protected function setAccessLevel(): AccessLevel
    {
        return AccessLevel::FullAccess;
    }

    public function setupKeyFields(): void
    {
    }

	public function setup(): void
    {
		$this->boxed = false;

        $this->setupFilterForm();

        $this->addButtons(
            (new ButtonSubmit('search', 'BTN_SEARCH', 'btn btn-primary'))->setName('save'),
            (new ButtonSubmit('resetSearch', 'BTN_CLEAR_FILTERS', 'btn btn-light ms-1'))->setName('reset')
        );
	}

	public function loadValues(): void
    {
		$values = Session::get($this->name);

		if (empty($values)) {
			$presets = $this->getFilterPresets();

			if (!empty($presets)) {
				$presets = array_keys($presets);
				$values = $this->getFilterPreset($presets[0]);

				Session::set($this->name, $values);
			}
		}

		if(!Empty($values)){
			$this->hasFilter = true;

            $this->values = $values;
		}
	}

	public function saveValues(): void
    {
        Session::set($this->name, $this->values);

		$presets = $this->getFilterPresets();
		if (!empty($presets)) {
			$presets = array_keys($presets);
			$this->saveFilterPreset($presets[0]);
		} else {
			$this->saveFilterPreset(null, 'default');
		}

        if($this->parentTable){
            Session::delete($this->parentTable . '-selections');
        }

        Router::pageRedirect('./');
	}

    public static function getFilterValues():false|array
    {
        $className = trim(strrchr(static::class, "\\"), "\\");

        return Session::get($className);
    }

	public function reset(): void
    {
        Session::delete($this->name);

		$presets = $this->getFilterPresets();
		if (!empty($presets)) {
			$presets = array_keys($presets);
			$this->values = [];
			$this->saveFilterPreset($presets[0]);
		}

		if($this->parentTable){
            Session::delete('table_settings_' . $this->parentTable);
            Session::delete($this->parentTable . '-selections');
		}

        $this->hasFilter = false;

        Router::pageRedirect('./');
	}

	private function saveFilterPreset(?int $id = null, string $name = ''):void
    {
		if (!empty($this->name) && $this->user->getId()) {
			if (empty($id)) {
				$this->db->sqlQuery(
					Db::insert(
						'filter_presets',
						[
							'fp_us_id'  => $this->user->getId(),
							'fp_filter' => $this->name,
							'fp_name'   => $name,
							'fp_values' => json_encode($this->values)
						]
					)
				);
			} else {
				$updateFields = [
					'fp_values' => json_encode($this->values)
				];
				if (!empty($name)) {
					$updateFields['fp_name'] = $name;
				}
				$this->db->sqlQuery(
					Db::update(
						'filter_presets',
						$updateFields,
						[
							'fp_us_id'  => $this->user->getId(),
							'fp_filter' => $this->name,
							'fp_id'     => $id
						]
					)
				);
			}
		}
	}

	private function getFilterPresets(): array
    {
		$result = [];

		if (!empty($this->name) && !empty($this->owner->user->id)) {
			$res = $this->db->getRows(
                Db::select(
                    'filter_presets',
                    [
                        'fp_id',
                        'fp_name'
                    ],
                    [
                        'fp_us_id' => $this->user->getId(),
                        'fp_filter' => $this->name
                    ]
                )
			);
			if (!empty($res)) {
				foreach($res as $row) {
					$result[$row['fp_id']] = $row['fp_name'];
				}
			}
		}

		return $result;
	}

	private function getFilterPreset(int $id):array
    {
		$result = [];

		if (!empty($id) && !empty($this->owner->user->id)) {
			$row = $this->db->getFirstRow(
                Db::select(
                    'filter_presets',
                    [
                        'fp_values'
                    ],
                    [
                        'fp_us_id' => $this->user->getId(),
                        'fp_id' => $id
                    ]
                )
			);
			if (!empty($row['fp_values'])) {
				$result = json_decode($row['fp_values'], true);
			}
		}

		return $result;
	}

}
