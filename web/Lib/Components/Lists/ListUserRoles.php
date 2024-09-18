<?php

namespace Framework\Components\Lists;

use Framework\Locale\Translate;

class ListUserRoles extends AbstractList
{
    private string|false $highestRole = false;

    private string $group = USER_GROUP_ADMINISTRATORS;

    protected function setup(): array
    {
        $list = [];
        $userLevel = 0;
        $level = 0;

        if (!empty($this->highestRole)) {
            foreach ($GLOBALS['USER_ROLES'][$this->group] as $key => $value) {
                if ($this->highestRole == $key) {
                    $userLevel = $level;
                    break;
                }
                $level++;
            }
        }

        $level = 0;
        foreach ($GLOBALS['USER_ROLES'][$this->group] as $key => $value) {
            if ($userLevel <= $level) {
                $list[$key] = [
                    'name' => Translate::get($value['label']),
                    'color' => $value['color'],
                ];
            }
            $level++;
        }

        return $list;
    }

    /**
     * Set the parameters for the list.
     *
     * @param array $params['highestRole', 'group'] The highest level of the role | group of roles
     * @return $this
     */
    public function setParams(array $params): self
    {
        if(!Empty($params['highestRole'])) $this->highestRole = (string)$params['highestRole'];

        if(!Empty($params['group'])) $this->group = (string)$params['group'];

        return $this;
    }
}