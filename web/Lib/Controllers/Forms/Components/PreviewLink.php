<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Components\Enums\FileType;
use Framework\Controllers\Forms\AbstractFormElement;

class PreviewLink extends AbstractFormElement
{
    const Type = 'preview';
    const PreviewUrl = '/ajax/preview/?';

    private FileType $type;

    private string $fileId;

    private string $fileName;

    private string $fileHash;

    public function __construct(string $id, FileType $fileType, string $label = '', string $class = '')
    {
        parent::__construct($id, $label, null, $class);

        $this->type = $fileType;
    }

    public function init():void
    {
        $this->notDBField();
    }

    public function getType(): string
    {
        return $this::Type;
    }

    public function getFileType():string
    {
        return $this->type->name;
    }

    public function getFileId():string
    {
        return $this->fileId;
    }

    public function getFileHash():string
    {
        return $this->fileHash;
    }

    public function getFileName():string
    {
        return ($this->fileName ?: $this->getLabel());
    }

    public function setFileData(string $fileId, string $fileHash, string $fileName = ''):self
    {
        $this->fileId = $fileId;
        $this->fileHash = $fileHash;
        $this->fileName = $fileName;

        return $this;
    }

    public function getPreviewUrl():string
    {
        return self::PreviewUrl . 'type=' . $this->getFileType() . '&id=' . $this->getFileId() . '&hash=' . $this->getFileHash();
    }
}