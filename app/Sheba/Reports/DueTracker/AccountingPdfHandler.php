<?php

namespace App\Sheba\Reports\DueTracker;

use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\Reports\Handler;
use Throwable;

class AccountingPdfHandler extends Handler
{
    use CdnFileManager;
    /** @var PDF $pdf*/
    private $pdf;
    private $downloadFormat = "pdf";
    protected $header;
    protected $footer;

    public function __construct()
    {
        $this->pdf = app('dompdf.wrapper');
    }

    /**
     * @param $header
     * @return $this
     */
    public function setHeader($header): AccountingPdfHandler
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param $footer
     * @return $this
     */
    public function setFooter($footer): AccountingPdfHandler
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * @return $this
     */
    public function create(): AccountingPdfHandler
    {
        $this->pdf->loadView($this->viewFileName, $this->data);
        return $this;
    }

    /**
     * @param $mPdf
     * @return Response|string|void
     * @throws MpdfException
     * @throws Throwable
     */
    public function download($mPdf = false)
    {
        if ($mPdf) {
            $mPDF=$this->getMpdf();
            $mPDF->simpleTables = true;
            $mPDF->packTableData = true;
            $mPDF->shrink_tables_to_fit = 1;
            $keep_table_proportions = TRUE;
            $data = view($this->viewFileName, $this->data)->render();
            $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);
            return $mPDF->Output("$this->filename.$this->downloadFormat", "d");
        }
        $this->create();

        return $this->pdf->download("$this->filename.$this->downloadFormat");
    }

    /**
     * @throws MpdfException
     */
    private function getMpdf(): Mpdf
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        return new Mpdf([
            'mode' => 'utf-8','format' => 'A4','margin_header' => 0,'margin_footer'=>0,
            'tempDir' => storage_path('app/temp'),
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts'),
            ]), 'fontdata' => $fontData + [
                    'kalpurush' => [
                        'R' => 'Siyamrupali.ttf', 'I' => 'Siyamrupali.ttf', 'useOTL' => 0xFF, 'useKashida' => 75,
                    ]
                ], 'default_font' => 'kalpurush','margin_left' => 0,
            'margin_right' => 0,
        ]);

    }

    /**
     * @param $mPdf
     * @return string
     * @throws MpdfException
     * @throws Throwable
     */
    public function save($mPdf = false): string
    {

        if (!is_dir(public_path('temp'))) {
            mkdir(public_path('temp'), 0777, true);
        }
        if ($mPdf) {
            $mPDF=$this->getMpdf();
            $mPDF->simpleTables = false;
            $mPDF->packTableData = true;
            $mPDF->setAutoTopMargin = 'stretch';
            $mPDF->setAutoBottomMargin = 'stretch';
            $mPDF->SetHTMLHeader($this->header, 'O');
            $mPDF->SetHTMLFooter($this->footer,'O');
            $data = view($this->viewFileName, $this->data)->render();
            $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);

            $folder = $this->folder ?: 'invoices/pdf/';
            $time = time();
            $file = $this->filename . "_$time." . $this->downloadFormat;
            $path = public_path('temp') . '/' . $file;
            $mPDF->Output($path, "F");
            $cdn = $this->saveFileToCDN($path, $folder, $file);
            File::delete($path);
            return $cdn;
        }
        $this->create();
        $folder = $this->folder ?: 'invoices/pdf/';
        $time = time();
        $file = $this->filename . "_$time." . $this->downloadFormat;
        $path = public_path('temp') . '/' . $file;
        $this->pdf->save($path);
        $cdn = $this->saveFileToCDN($path, $folder, $file);
        File::delete($path);
        return $cdn;
    }

    /**
     * @return string
     */
    protected function getViewPath(): string
    {
        return $this->viewPath ?: "reports.pdfs.";
    }
}