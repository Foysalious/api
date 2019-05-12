<?php namespace Sheba\Reports;

use Barryvdh\DomPDF\PDF;

class PdfHandler extends Handler
{
    /** @var PDF */
    private $pdf;
    private $downloadFormat = "pdf";

    public function __construct()
    {
        $this->pdf = app('dompdf.wrapper');
    }

    public function create()
    {
        $this->pdf->loadView($this->viewFileName, $this->data);
        return $this;
    }

    public function download()
    {
        $this->create();
        return $this->pdf->download("$this->filename.$this->downloadFormat");
    }

    public function save()
    {

    }

    protected function getViewPath()
    {
        return "reports.pdfs.";
    }
}