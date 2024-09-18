<?php
class MPdfGenerator extends PdfGenerator {
    const PAGE_BREAK = '<pagebreak>';

    private \Mpdf\Mpdf $pdf;

    public function __construct(string $fileMode, array $options = [])
    {
        parent::__construct($fileMode, $options);

        $this->options['format'] = 'A4';

        $this->pdf = new \Mpdf\Mpdf($options);

        $this->pdf->SetDisplayMode('fullpage');
    }

    public function createPdf(string $fileName, string $html, array $fixTexts = [])
    {
        if($this->template) {
            $this->pdf->SetDocTemplate($this->template, true);
        }

        if (!empty($this->header)) {
            $this->pdf->SetHTMLHeader($this->header);
        }

        $this->pdf->WriteHTML($html);

        if($fixTexts){
            foreach($fixTexts AS $data){
                if($data['html'] == self::PAGE_BREAK){
                    $this->pdf->AddPage();
                }else{
                    $this->pdf->WriteFixedPosHTML($data['html'], $data['x'], $data['y'], $data['w'], $data['h'], $data['overflow']);
                }
            }
        }

        if (!empty($this->footer)) {
            $this->pdf->SetHTMLFooter($this->footer);
        }

        if ($this->fileMode == Docs::PDF_STRING) {
            return $this->pdf->Output($fileName, $this->fileMode);
        } else {
            $this->pdf->Output($fileName, $this->fileMode);
            return true;
        }
    }

    public function showPrintDialog(bool $show)
    {
        if($show) {
            $this->pdf->SetJS('this.print();');
        }
    }
}