<?php

namespace Framework\Controllers\Forms\Components;

use Framework\Controllers\Forms\AbstractFormElement;

class Link extends AbstractFormElement
{
    const Type = 'link';

    private string $url = '';

    private bool $preview = false;

    private int $docId = 0;

    private int $docGroupId = 0;

    private string $documentType = '';

    private string $fileHash = '';

    public function __construct(string $id, string $label = '', string $url = '', string $class = '')
    {
        parent::__construct($id, $label, '', $class);

        $this->setUrl($url);
    }

    public function init():void
    {
        $this->notDBField();
    }

    public function getType(): string
    {
        return $this::Type;
    }

    public function setDocumentType(string $type): self
    {
        $this->documentType = $type;

        return $this;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function setFile(int $docId, int $docGroupId, string $hash):self
    {
        $this->docId = $docId;

        $this->docGroupId = $docGroupId;

        $this->fileHash = $hash;

        return $this;
    }

    public function getDocGroupId():int
    {
        return $this->docGroupId;
    }

    public function getDocId():int
    {
        return $this->docId;
    }

    public function getFileHash():string
    {
        return $this->fileHash;
    }

    public function setUrl(string $url):self
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl():string
    {
        if ($this->fileHash && $this->docId && $this->docGroupId) {
            $this->url = $this->getDownloadUrl();
        }

        return $this->url;
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

    public function getDownloadUrl():string
    {
        return '/download/?' . $this->getFileSource();
    }

    public function getPreviewUrl():string
    {
        return '/ajax/preview/?' . $this->getFileSource();
    }

    private function getFileSource():string
    {
        return 'docid=' . $this->docId . '&id=' . $this->docGroupId . '&src=' . $this->fileHash . (!Empty($this->documentType) ? '&type=' . $this->documentType : '');
    }

    public function setTarget(string $target):self
    {
        $this->addAttribute('target', $target);

        return $this;
    }

}