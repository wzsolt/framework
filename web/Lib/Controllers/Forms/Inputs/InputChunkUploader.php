<?php
namespace Framework\Controllers\Forms\Inputs;

class InputChunkUploader extends AbstractFileUpload
{
    const Type = 'chunkUploader';

    private array $allowedExtensions = [];

    private string $defaultFile = '';

    protected function init():void
    {
        $this->notDBField();
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setAllowedExtensions(array $extensions):self
    {
        $this->allowedExtensions = $extensions;

        return $this;
    }

    public function getAllowedExtensions():string
    {
        return implode(', ', $this->allowedExtensions);
    }

    public function setDefaultFile($fileName):self
    {
        $this->defaultFile = $fileName;

        return $this;
    }

    public function getDefaultFile():string
    {
        return $this->defaultFile;
    }
}