<?php
use HeadlessChromium\Page;
use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;

class ChromePdfGenerator extends PdfGenerator {
    const TIME_OUT = 100000;

    private Browser $browser;

    private string $tmpFileName;

    public function __construct(string $fileMode, array $options = [])
    {
        parent::__construct($fileMode, $options);

        if(defined('CHROME_BINARY')){
            $chromeBinary = CHROME_BINARY;
        }else{
            $chromeBinary = null;
        }

        $browserFactory = new BrowserFactory($chromeBinary);
        $browserFactory->setOptions(['sendSyncDefaultTimeout' => self::TIME_OUT]);
        $this->browser = $browserFactory->createBrowser([
            'noSandbox' => true,
            'headless' => true,
            'enableImages' => true
        ]);

    }

    public function createPdf(string $fileName, string $html, array $fixTexts = [])
    {
        try {
            $page = $this->browser->createPage();

            //$page->navigate($this->generateFromHtml($html))->waitForNavigation(Page::LOAD, self::TIME_OUT);
            $page->navigate($this->generateFromTempFile($html))->waitForNavigation(Page::LOAD, self::TIME_OUT);

            $options = [
                'landscape'           => $this->isLandscape,   // default to false
                'printBackground'     => true,             // default to false
                'displayHeaderFooter' => ($this->header || $this->footer),             // default to false
                'preferCSSPageSize'   => false,             // default to false (reads parameters directly from @page)
                'marginTop'           => 1.4,              // defaults to ~0.4 (must be a float, value in inches)
                'marginBottom'        => 1.0,              // defaults to ~0.4 (must be a float, value in inches)
                'marginLeft'          => 0.9,              // defaults to ~0.4 (must be a float, value in inches)
                'marginRight'         => 0.9,              // defaults to ~0.4 (must be a float, value in inches)
                'paperWidth'          => 8.268,            // defaults to 8.5 (must be a float, value in inches)
                'paperHeight'         => 11.693,           // defaults to 8.5 (must be a float, value in inches)
                'headerTemplate'      => $this->header,
                'footerTemplate'      => $this->footer,
                'scale'               => 1.0,              // defaults to 1.0 (must be a float)
            ];

            $options = array_merge($options, $this->options);
            $pdf = $page->pdf($options);

            switch ($this->fileMode){
                case Docs::PDF_DOWNLOAD:
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename=' . basename($fileName));
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');

                    echo base64_decode($pdf->getBase64());
                    break;

                case Docs::PDF_FILE:
                    $pdf->saveToFile($fileName, self::TIME_OUT);
                    break;

                case Docs::PDF_INLINE:
                    header('Content-Type: application/pdf');
                    header('Content-disposition: inline; filename="' . basename($fileName) . '"');
                    header('Cache-Control: public, must-revalidate, max-age=0');
                    header('Pragma: public');
                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

                    echo base64_decode($pdf->getBase64());
                    break;

                case Docs::PDF_STRING:
                    return base64_decode($pdf->getBase64());
            }

        } finally {
            // bye
            $this->browser->close();

            if(file_exists($this->tmpFileName)){
                unlink($this->tmpFileName);
            }
        }

        return true;
    }

    public function showPrintDialog(bool $show)
    {
    }

    private function generateFromHtml($html)
    {
        return 'data:text/html;base64,' . base64_encode($html);
    }

    private function generateFromFile($file)
    {
        if ($file[0] !== '/') {
            $file = getcwd() . '/' . $file;
        }

        return 'file://' . $file;
    }

    private function generateFromTempFile(string $html)
    {
        $this->tmpFileName = DIR_CACHE . microtime() . '-' . generateRandomString() . '.html';
        file_put_contents($this->tmpFileName, $html);

        return $this->generateFromFile($this->tmpFileName);
    }
}