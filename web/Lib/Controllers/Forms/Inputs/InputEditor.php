<?php
namespace Framework\Controllers\Forms\Inputs;

use Framework\Controllers\Forms\AbstractFormElement;

class InputEditor extends AbstractFormElement
{
    const Type = 'editor';

    const tbBold = 'bold';
    const tbItalic = 'italic';
    const tbUnderline = 'underline';
    const tbFontSize = 'fontsize';
    const tbClear = 'clear';
    const tbStyle = 'style';
    const tbUndo = 'undo';
    const tbRedo = 'redo';
    const tbTextColor = 'color';
    const tbUnorderedList = 'ul';
    const tbOrderedList = 'ol';
    const tbParagraph = 'paragraph';
    const tbTable = 'table';
    const tbLink = 'link';
    const tbGallery = 'gallery';
    const tbVideo = 'video';
    const tbFullscreen = 'fullscreen';
    const tbCodeView = 'codeview';
    const tbCleaner = 'cleaner';

    private array $toolbar = [];

    protected function init():void
    {
        $this->addClass('htmleditor');

        //$this->addCss('summernote/summernote-bs5.min.css', false, 'summernote');
        //$this->addJs('summernote/summernote-bs5.js', false, 'summernote');

        $this->addCss('summernote/summernote-lite.min.css', false, 'summernote');
        $this->addJs('summernote/summernote-lite.js', false, 'summernote');

        $this->addJs('summernote/summernote-cleaner.js', false, 'summernote-cleaner');
    }

    public function hasGallery():self
    {
        $this->addJs('summernote/summernote-gallery-extension.js', false, 'summernote-gallery');
        $this->addCss('fileuploader/jquery.fileuploader.min.css', false, 'fileuploader');
        $this->addCss('fileuploader/fileuploader-theme-thumbnails.css', false, 'fileuploader');
        $this->addCss('fileuploader/fileuploader-theme-gallery.css', false, 'fileuploader-gallery');
        $this->addJs('fileuploader/jquery.fileuploader.min.js', false, 'fileuploader');

        return $this;
    }

    public function getType():string
    {
        return $this::Type;
    }

    public function setHeight(int $height):self
    {
        $this->addData('height', $height);

        return $this;
    }

    public function setToolbar(string $groupName, array $toolbar):self
    {
        $this->toolbar[$groupName] = $toolbar;

        return $this;
    }

    public function getToolbar():string
    {
        return ($this->toolbar ? json_encode($this->toolbar) : '');
    }
}