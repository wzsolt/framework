<?php

namespace Framework\Components\Menu;

use Framework\Components\Enums\Color;

class MenuItem
{
    const MENU_TYPE_ITEM  = 1;

    const MENU_TYPE_GROUP = 2;

    const MENU_TYPE_LABEL = 10;

    const MENU_TYPE_SEPARATOR = 11;

    const MENU_POSITION_HIDDEN = 1;

    const MENU_POSITION_HEADER = 2;

    const MENU_POSITION_FOOTER = 4;

    const MENU_POSITION_SIDEBAR = 8;

    const MENU_POSITION_ALL = self::MENU_POSITION_SIDEBAR + self::MENU_POSITION_HEADER + self::MENU_POSITION_FOOTER;

    const MENU_POSITIONS = [
        self::MENU_POSITION_SIDEBAR => 'LBL_MENU_SIDEBAR',
        self::MENU_POSITION_HEADER  => 'LBL_MENU_HEADER',
        self::MENU_POSITION_FOOTER  => 'LBL_MENU_FOOTER'
    ];

    private string $key;

    private string $label;

    private int $displayType;

    private string $layout = 'default';

    private string $pageModel;

    private int $badge = 0;

    private Color $badgeColor;

    private string $icon = '';

    private bool $requireLogin;

    private bool $isSelected = false;

    private string $url = '';

    private bool $openNewTab = false;

    private string $path = '';

    private array $userGroups = [];

    protected bool $isContainer = false;

    private bool $hasAccess = false;

    private int $position = 0;

    private array $items = [];

    public function __construct(string $key, bool $requireLogin = false, int $position = self::MENU_POSITION_HEADER)
    {
        $this->setKey($key);

        $this->setRequireLogin($requireLogin);

        $this->setDisplayType(self::MENU_TYPE_ITEM);

        $this->setPosition($position);

        $this->setLabel('MENU_' . strtoupper($key));
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): MenuItem
    {
        $this->key = $key;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): MenuItem
    {
        $this->label = $label;

        return $this;
    }

    public function getDisplayType(): int
    {
        return $this->displayType;
    }

    public function setDisplayType(int $displayType): self
    {
        $this->displayType = $displayType;

        return $this;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(string $layout): MenuItem
    {
        $this->layout = $layout;

        return $this;
    }

    public function getPageModel(): string
    {
        return ($this->pageModel ?? $this->key);
    }

    public function setPageModel(string $pageModel): MenuItem
    {
        $this->pageModel = $pageModel;

        return $this;
    }

    public function getBadge(): int
    {
        return $this->badge;
    }

    public function setBadge(int $badge): MenuItem
    {
        $this->badge = $badge;

        return $this;
    }

    public function getBadgeColor(): Color
    {
        return $this->badgeColor;
    }

    public function setBadgeColor(Color $badgeColor): MenuItem
    {
        $this->badgeColor = $badgeColor;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): MenuItem
    {
        $this->icon = $icon;

        return $this;
    }

    public function isRequireLogin(): bool
    {
        return $this->requireLogin;
    }

    public function setRequireLogin(bool $access): MenuItem
    {
        $this->requireLogin = $access;

        return $this;
    }

    public function getUserGroups(): array
    {
        return $this->userGroups;
    }

    public function setUserGroups(array|string $userGroups): MenuItem
    {
        if(!is_array($userGroups)) $userGroups = [$userGroups];

        $this->setRequireLogin(true);

        $this->userGroups = $userGroups;

        return $this;
    }

    public function addUserGroup(string $userGroup): MenuItem
    {
        if(!in_array($userGroup, $this->userGroups)) {
            $this->userGroups[] = $userGroup;
        }

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): MenuItem
    {
        $this->url = $url;

        return $this;
    }

    public function isOpenNewTab(): bool
    {
        return $this->openNewTab;
    }

    public function setOpenNewTab(bool $openNewTab): MenuItem
    {
        $this->openNewTab = $openNewTab;

        return $this;
    }

    public function getPath(): string
    {
        return (!Empty($this->url) ? $this->url : $this->path);
    }

    public function setPath(string $path): MenuItem
    {
        $this->path = $path;

        return $this;
    }

    public function isContainer(): bool
    {
        return $this->isContainer;
    }

    public function itemCount(): int
    {
        return count($this->items);
    }

    public function isSelected(): bool
    {
        return $this->isSelected;
    }

    public function setIsSelected(bool $isSelected = true): MenuItem
    {
        $this->isSelected = $isSelected;

        return $this;
    }

    public function isHasAccess(): bool {
        return $this->hasAccess;
    }

    public function setHasAccess(bool $hasAccess): MenuItem
    {
        $this->hasAccess = $hasAccess;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position = self::MENU_POSITION_HEADER): MenuItem
    {
        $this->position = $position;

        return $this;
    }

    public function addItems(MenuItem ...$items):MenuItem
    {
        $this->isContainer = true;

        $this->setDisplayType(MenuItem::MENU_TYPE_GROUP);

        foreach($items AS $item) {
            $this->items[$item->getKey()] = $item;
        }

        return $this;
    }

    public function hasItems():bool
    {
        return !Empty($this->items);
    }

    /**
     * @return MenuItem[]
     */
    public function getItems():array
    {
        return $this->items;
    }

    public function setItems(array $items):MenuItem
    {
        $this->setDisplayType(MenuItem::MENU_TYPE_GROUP);

        $this->items = $items;

        return $this;
    }

    /**
     * @return MenuItem[]
     */
    public function &getItemsByRef():array
    {
        return $this->items;
    }

}