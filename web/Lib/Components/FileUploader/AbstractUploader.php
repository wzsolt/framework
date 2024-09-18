<?php

namespace Framework\Components\FileUploader;

use Framework\Components\Uuid;

abstract class AbstractUploader
{
    const FORM_NAME = 'fileUploader';

    const THEME_THUMBNAILS = 'thumbnails';
    const THEME_GALLERY = 'gallery';
    const THEME_AVATAR = 'avatar';

    protected int $id = 0;

    private int $fileMaxSize = FILEUPLOAD_MAX_FILESIZE;

    private int $maxSize = 0;

    private int $fileLimit = 0;

    private array $allowedExtensions = [];

    private array $disallowedExtensions = [];

    private string $uploadDir;

    private bool $replace = true;

    private bool $isRequired = false;

    private string $title = '';

    abstract protected function init():self;

    abstract protected function doUpload(array $uploadData, $fileId = false): array;

    abstract protected function doDelete(int $fileId): void;

    abstract protected function doSort(array $list): void;

    abstract protected function setDefault(int $fileId): array;

    abstract public function loadFiles(): array;

    abstract protected function doRename(int $fileId, string $title): array;

    abstract protected function doEdit(int $fileId, array $options): void;

    public function load($id): array
    {
        $this->id = $id;

        $this->init();

        return $this->loadFiles();
    }

    public function upload(int $id, string $uploaderFormName): array
    {
        $this->id = $id;

        $this->init();

        $fileUploader = new FileUploader($uploaderFormName, $this->getSetup());

        return $this->doUpload($fileUploader->upload());
    }

    public function delete(int $id, int $fileId):void
    {
        $this->id = $id;

        $this->init()->doDelete($fileId);
    }

    public function sort(int $id, array $list):void
    {
        $this->id = $id;

        $this->init()->doSort($list);
    }

    public function mark(int $id, int $fileId):array
    {
        $this->id = $id;

        return $this->init()->setDefault($fileId);
    }

    public function rename(int $id, int $fileId, string $title):array
    {
        $this->id = $id;

        return $this->init()->doRename($fileId, $title);
    }

    public function edit(int $id, int $fileId, array $options):void
    {
        $this->id = $id;

        $this->init()->doEdit($fileId, $options);
    }

    protected function getSetup():array
    {
        $title = $this->getTitle();

        if(!$title){
            $title = function($item){
                return UUID::v4();
            };
        }

        return [
            'fileMaxSize'   => $this->getFileMaxSize(),
            'maxSize'       => $this->getMaxSize(),
            'limit'         => $this->getFileLimit(),
            'extensions'    => $this->getAllowedExtensions(),
            'disallowedExtensions'    => $this->getDisallowedExtensions(),
            'uploadDir'     => $this->getUploadDir(),
            'replace'       => $this->isReplace(),
            'required'      => $this->isRequired,
            'title'         => $title
        ];

    }

    public function getFileMaxSize(): int
    {
        return $this->fileMaxSize;
    }

    protected function setFileMaxSize(int $fileMaxSize): self
    {
        $this->fileMaxSize = $fileMaxSize;

        return $this;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    protected function setMaxSize(int $maxSize): self
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    public function getFileLimit(): int
    {
        return $this->fileLimit;
    }

    protected function setFileLimit(int $fileLimit): self
    {
        $this->fileLimit = $fileLimit;

        return $this;
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    protected function setAllowedExtensions(array $allowedExtensions): self
    {
        $this->allowedExtensions = $allowedExtensions;

        return $this;
    }

    public function getDisallowedExtensions(): array
    {
        return $this->disallowedExtensions;
    }

    protected function setDisallowedExtensions(array $disallowedExtensions): self
    {
        $this->disallowedExtensions = $disallowedExtensions;

        return $this;
    }

    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }

    protected function setUploadDir(string $uploadDir): self
    {
        $this->uploadDir = $uploadDir;

        if (!file_exists($this->uploadDir)) {
            @mkdir($this->uploadDir, 0777, true);
            @chmod($this->uploadDir, 0777);
        }

        return $this;
    }

    public function isReplace(): bool
    {
        return $this->replace;
    }

    protected function setReplace(bool $replace): self
    {
        $this->replace = $replace;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    protected function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    protected function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}