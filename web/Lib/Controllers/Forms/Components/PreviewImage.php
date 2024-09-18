<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Components\Enums\Crop;
use Framework\Controllers\Forms\AbstractFormElement;

class PreviewImage extends AbstractFormElement
{
    const Type = 'previewImage';
    const PreviewUrl = '/ajax/preview/?';

    private string $path;

    private string|false $src = false;

    private bool $preview = true;

    private array $imgSize = [];

    private Crop $cropMode;

    private bool $responsive = false;

    public function __construct(string $id, string $class = '')
    {
        parent::__construct($id, false, '', $class);
    }

    public function init():void
    {
        $this->imgSize = [
            'width'  => false,
            'height' => false,
        ];
    }

    public function getType(): string
    {
        return $this::Type;
    }

    public function setPath(string $path):self
    {
        $this->path = rtrim($path, '/') . '/';

        return $this;
    }

    public function getPath():string
    {
        return $this->path;
    }

    public function setSrc(string $src):self
    {
        $this->src = $src;

        return $this;
    }

    public function getSrc():string
    {
        return $this->src;
    }

    public function setResponsive(bool $responsive):self
    {
        $this->responsive = $responsive;

        return $this;
    }

    public function isResponsive():bool
    {
        return $this->responsive;
    }

    public function setCropMode(Crop $cropMode):self
    {
        $this->cropMode = $cropMode;

        return $this;
    }

    public function getCropMode():int
    {
        return $this->cropMode->value;
    }

    public function setPreview(bool $preview = true):self
    {
        $this->preview = $preview;

        return $this;
    }

    public function hasPreview():bool
    {
        return $this->preview;
    }

    public function setSize(int|false $width = false, int|false $height = false):self
    {
        $this->imgSize = [
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    public function getSize():array
    {
        return $this->imgSize;
    }

    public function getPreviewUrl():string
    {
        return self::PreviewUrl . 'src=';
    }

}