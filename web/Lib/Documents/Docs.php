<?php

abstract class Docs
{
	const PDF_INLINE 	= 'I';
	const PDF_DOWNLOAD 	= 'D';
	const PDF_FILE 		= 'F';
	const PDF_STRING 	= 'S';

    private string $fileMode = self::PDF_INLINE;

    private string $fileName = 'doc.pdf';

    private string $path = '';

    private array $options = [];

    private string $pdfTemplate = '';

    private string $template = '';

    private array $data = [];

    private string $headerTemplate = '';

    private string $headerHtml = '';

    private string $footerTemplate = '';

    private string $footerHtml = '';

    private string $html = '';

    private array $fixedTexts = [];

    private bool $showPrintDialog = false;

    abstract protected function loadContent():array;

    public function __construct()
    {
        ini_set("pcre.backtrack_limit", "5000000");
        set_time_limit(0);
    }

	protected function setOptions(array $options):self
    {
        foreach($options AS $key => $value){
            $this->setOption($key, $value);
        }

        return $this;
    }

    protected function setOption(string $key, $value):self
    {
        $this->options[$key] = $value;

        return $this;
    }

    protected function setData($data):self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    protected function setValue(string $key, $value):self
    {
        $this->data[$key] = $value;

        return $this;
    }

    protected function addFixedText($html, $x = 0, $y = 0, $w = 0, $h = 0, $overflow = 'auto'):self
    {
        $this->fixedTexts[] = [
            'html' => $html,
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'h' => $h,
            'overflow' => $overflow,
        ];

        return $this;
    }

    protected function setHtml(string $html):self
    {
        $this->html = $html;

        return $this;
    }

	protected function getHtml():string
    {
		return $this->html;
	}

    protected function setHeader(string $html):self
    {
        $this->headerHtml = $html;

        return $this;
    }

    protected function setHeaderTemplate(string $fileName):self
    {
        $this->headerTemplate = $fileName;

        return $this;
    }

    protected function getHeader():string
    {
        if($this->headerTemplate){
            $this->headerHtml = $this->owner->view->renderContent($this->headerTemplate, $this->data, false);
        }

        return $this->headerHtml;
    }

    protected function setFooter(string $html):self
    {
        $this->footerHtml = $html;

        return $this;
    }

    protected function setFooterTemplate(string $fileName):self
    {
        $this->footerTemplate = $fileName;

        return $this;
    }

    protected function getFooter():string
    {
        if($this->footerTemplate){
            $this->footerHtml = $this->owner->view->renderContent($this->footerTemplate, $this->data, false);
        }

        return $this->footerHtml;
    }

    protected function setTemplate(string $template):self
    {
        $this->template = $template;

        return $this;
    }

    protected function getTemplate($template):array
    {
        $content = $this->owner->lib->getTemplate($template);

        if($content['template']){
            $this->template = $content['template'];
        }else{
            $this->template = $content['tag'];
        }

        $this->setValue('title', $content['title']);
        $this->setValue('template', $content['text']);

        return $content;
    }

    public function setFileName(string $fileName):self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileName():string
    {
        return $this->fileName;
    }

    public function getFilePath():string
    {
        return $this->path . $this->fileName;
    }

    public function setPdfTemplate(string $template):self
    {
        $this->pdfTemplate = $template;

        return $this;
    }

    public function print(bool $showPrintDialog = true):self
    {
        $this->showPrintDialog = $showPrintDialog;

        return $this;
    }

	public function setPath(string $path):self
    {
		$this->path = rtrim($path, '/') . '/';

		if(!is_dir($this->path)){
			@mkdir($this->path, 0777, true);
			@chmod($this->path, 0777);
		}

		return $this;
	}

	public function setFileMode(string $mode = self::PDF_INLINE):self
    {
		$this->fileMode = $mode;

		return $this;
	}

    public function getPDF()
    {
        if(!$this->html) {
            $this->renderContent();
        }

        if (isset($_REQUEST['debug'])) {
            if ($_REQUEST['debug'] == 'data') {
                dd($this->data);
            }
            if ($_REQUEST['debug'] == 'html') {
                print str_replace(['<pagebreak />', '<div class="page-break"></div>'], '<hr style="margin:40px 0;border: 4px dashed red;background-color: white;opacity: 1;">', $this->getHtml());
                exit();
            }
        }

        //$pdf = new MPdfGenerator($this->fileMode, $this->options);
        $pdf = new ChromePdfGenerator($this->fileMode, $this->options);
        $pdf->setDocTemplateFile($this->pdfTemplate);
        $pdf->showPrintDialog($this->showPrintDialog);
        $pdf->setHeader($this->getHeader());
        $pdf->setFooter($this->getFooter());

        if($html = $this->getHtml()) {
            return $pdf->createPdf($this->getFilePath(), $html, $this->fixedTexts);
        }

        return false;
    }

    public function renderContent():self
    {
        $this->setData($this->loadContent());

        $this->setValue('clientId', $this->owner->clientId);
        $this->setValue('domain', $this->owner->domain);
        $this->setValue('fileName', $this->fileName);

        if($this->template) {
            $this->setHtml($this->owner->view->renderContent($this->template, $this->data, false));
        }

        return $this;
    }

    private function saveHtml(){

    }
}
