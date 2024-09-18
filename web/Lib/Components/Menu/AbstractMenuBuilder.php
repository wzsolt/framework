<?php

namespace Framework\Components\Menu;

use Framework\Components\HostConfig;
use Framework\Models\Database\Db;

abstract class AbstractMenuBuilder
{
    private array $items = [];

    private string $page;

    private string $originalPage;

    abstract public function setup():void;

    protected function addItems(MenuItem ...$items):void
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    protected function loadMenu(bool $restrictedMenusOnly = false):void
    {
        $menu = Db::create()->getRows(
            Db::select(
                'menu',
                [
                    'm_id AS id',
                    'm_title AS title',
                    'm_slug AS slug',
                    'm_url AS url',
                    'm_access AS requireLogin',
                    'm_new_tab AS newTab',
                    'm_position AS position',
                    'm_page_model AS pageModel',
                ],
                [
                    'm_visible' => 1,
                    'm_access' => ($restrictedMenusOnly ? 1 : 0),
                    'm_language' => HostConfig::create()->getLanguage(),
                    'm_client_id' => HostConfig::create()->getClientId(),
                    'm_hosts' => [
                        'like' => '%|' . HostConfig::create()->getId() . '|%'
                    ]
                ],
                [],
                '',
                'm_order'
            )
        );
        if($menu){
            foreach ($menu as $row) {

                $position = (int) $row['position'];
                if(!$position){
                    $position = MenuItem::MENU_POSITION_HIDDEN;
                }

                $item = new MenuItem($row['slug'], (bool)$row['requireLogin'], $position);
                $item->setLabel($row['title']);

                if(!Empty($row['url'])){
                    $item->setUrl($row['url']);
                    $item->setOpenNewTab((bool)$row['newTab']);
                }else {
                    $pageModel = ($row['pageModel'] ?: 'content');
                    $item->setPageModel($pageModel);

                    $menuItems = $this->loadSubmenu($row['id'], $pageModel);
                    if($menuItems){
                        foreach($menuItems AS $menuItem){
                            $item->addItems($menuItem);
                        }
                    }
                }

                $this->addItems($item);
            }
        }

        $menuItems = $this->loadSubmenu(0);
        if($menuItems){
            foreach($menuItems AS $menuItem){
                $this->addItems($menuItem);
            }
        }
    }

    /**
     * @return MenuItem[]
     */
    private function loadSubmenu(int $menuId, string $pageModel = 'content'):array
    {
        $items = [];

        $menu = Db::create()->getRows(
            Db::select(
                'contents',
                [
                    'c_id AS id',
                    'c_title AS title',
                    'c_page_url AS slug',
                    'c_access AS requireLogin',
                    'c_position AS position',
                ],
                [
                    'c_m_id' => $menuId,
                    'c_published' => 1,
                    'c_home' => 0,
                    'c_language' => HostConfig::create()->getLanguage(),
                    'c_client_id' => HostConfig::create()->getClientId(),
                    'c_hosts' => [
                        'like' => '%|' . HostConfig::create()->getId() . '|%'
                    ]
                ],
                [],
                '',
                'c_order'
            )
        );
        if($menu){
            foreach ($menu as $row) {
                $position = (int) $row['position'];
                if(!$position){
                    $position = MenuItem::MENU_POSITION_HIDDEN;
                }

                $item = new MenuItem($row['slug'], (bool)$row['requireLogin'], $position);
                $item->setLabel($row['title']);
                $item->setPageModel($pageModel);

                $items[] = $item;
            }
        }

        return $items;
    }

    private function addItem(MenuItem $item):void
    {
        $this->items[$item->getKey()] = $item;
    }

    /**
     * @return MenuItem[]
     */
    public function getItems():array
    {
        return $this->items;
    }

    public function buildMenu(?string $userGroup = null, ?array $accessRights = [], int|false $position = false):array
    {
        if(Empty($this->items)) {
            $this->setup();
        }

        return $this->iterateMenu($this->items, '/', $userGroup, $accessRights, $position);
    }

    private function iterateMenu(array $items, string $parentKey, ?string $userGroup = null, ?array $accessRights = [], int|false $position = false):array
    {
        $menu = [];

        /**
         * @var $item MenuItem
         */

        foreach($items AS $key => $item){
            if($position === false || (($item->getPosition() & $position) == $position)) {
                $item->setPath($parentKey . $key . '/');
                $itemUserGroups = $item->getUserGroups();
                if(Empty($itemUserGroups) || (!Empty($userGroup) && in_array($userGroup, $itemUserGroups))) {
                    if(!$item->isRequireLogin() || !Empty($accessRights[$key]) || !$accessRights || $item->getDisplayType() == MenuItem::MENU_TYPE_GROUP || $item->getDisplayType() == MenuItem::MENU_TYPE_LABEL || $item->getDisplayType() == MenuItem::MENU_TYPE_SEPARATOR){
                        $menu[$key] = clone $item;

                        if ($item->isContainer()) {
                            $menu[$key]->setItems($this->iterateMenu($item->getItems(), $item->getPath(), $userGroup, $accessRights, $position));

                            if($item->getDisplayType() == 2 && $menu[$key]->itemCount() == 0){
                                unset($menu[$key]);
                            }
                        }
                    }
                }
            }
        }

        return $menu;
    }

    public function getItem(string $key):MenuItem|false
    {
        if($item = $this->findItem($this->items, $key)) {
            return $item;
        }

        return false;
    }

    public function getCurrentMenu():MenuItem|false
    {
        if($item = $this->findItem($this->items, $this->page)) {
            return $item;
        }

        return false;
    }

    private function findItem(&$object, string $key):MenuItem|false
    {
        $result = false;

        foreach($object AS $elementId => &$element){
            if($elementId == $key){
                return $element;
            }else {
                if ($element instanceof MenuItem) {
                    if ($element->isContainer() && !$result) {
                        $result = $this->findItem($element->getItemsByRef(), $key);
                    }
                } else {
                    if(!$result) {
                        $result = $this->findItem($element, $key);
                    }
                }
            }
        }

        return $result;
    }

    public function getPage(): string
    {
        return $this->page;
    }

    public function setPage(string $page, string $originalPage = ''): self
    {
        $this->page = $page;

        $this->originalPage = $originalPage;

        return $this;
    }

    public function getOriginalPage(): string
    {
        return $this->originalPage;
    }

}