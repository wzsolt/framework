<?php
abstract class PdfGenerator {
    protected array $options = [];

    protected string $fileMode;

    protected string $header = '';

    protected string $footer = '';

    protected bool $isLandscape = false;

    protected string $template;

    abstract public function createPdf(string $fileName, string $html, array $fixTexts = []);

    abstract public function showPrintDialog(bool $show);

    public function __construct(string $fileMode, array $options = [])
    {
        $this->fileMode = $fileMode;

        $this->options = $options;
    }

    public function setHeader(string $html):self
    {
        $this->header = $html;

        return $this;
    }

    public function setFooter(string $html):self
    {
        $this->footer = $html;

        return $this;
    }

    public function setDocTemplateFile(string $template):self
    {
        $this->template = $template;

        return $this;
    }

    public function setLandscapeMode(bool $isLandscape):self
    {
        $this->isLandscape = $isLandscape;

        return $this;
    }
}