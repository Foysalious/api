<?php namespace Sheba\Reports;

use Barryvdh\DomPDF\PDF;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Sheba\FileManagers\CdnFileManager;
use Throwable;

class PdfHandler extends Handler
{
    use CdnFileManager;

    /** @var PDF $pdf */
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

    /**
     * @param bool $mPdf
     * @return Response|string
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
            $keep_table_proportions     = TRUE;
            $data                       = view($this->viewFileName, $this->data)->render();
            $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);
            return $mPDF->Output("$this->filename.$this->downloadFormat", "d");
        }
        $this->create();

        return $this->pdf->download("$this->filename.$this->downloadFormat");
    }

    /**
     * @throws MpdfException
     */
    private function getMpdf()
    {
        $defaultConfig     = (new ConfigVariables())->getDefaults();
        $fontDirs          = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData          = $defaultFontConfig['fontdata'];
        return new Mpdf([
            'mode' => 'utf-8',
            'tempDir' => storage_path('app/temp'),
            'fontDir' => array_merge($fontDirs, [
                storage_path('/fonts'),
            ]), 'fontdata'        => $fontData + [
                    'kalpurush' => [
                        'R' => 'Siyamrupali.ttf', 'I' => 'Siyamrupali.ttf', 'useOTL' => 0xFF, 'useKashida' => 75,
                    ]
                ], 'default_font' => 'kalpurush'
        ]);

    }

    /**
     * @throws MpdfException
     * @throws Throwable
     */
    public function save($mPdf = false)
    {
        if (!is_dir(public_path('temp'))) {
            mkdir(public_path('temp'), 0777, true);
        }
        if ($mPdf) {
            $mPDF                       = $this->getMpdf();
            $mPDF->simpleTables         = true;
            $mPDF->packTableData        = true;
            $mPDF->shrink_tables_to_fit = 1;
            $data                       = view($this->viewFileName, $this->data)->render();
            $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);

            $folder = $this->folder ?: 'invoices/pdf/';
            $time   = time();
            $file   = $this->filename . "_$time." . $this->downloadFormat;
            $path   = public_path('temp') . '/' . $file;
            $mPDF->Output($path, "F");
            $cdn = $this->saveFileToCDN($path, $folder, $file);
            File::delete($path);
            return $cdn;
        }
        $this->create();
        $folder = $this->folder ?: 'invoices/pdf/';
        $time   = time();
        $file   = $this->filename . "_$time." . $this->downloadFormat;
        $path   = public_path('temp') . '/' . $file;
        $this->pdf->save($path);
        $cdn = $this->saveFileToCDN($path, $folder, $file);
        File::delete($path);
        return $cdn;
    }

    public function saveInvoice($type, $mPdf = false)
    {
        $this->data['type'] = $type;
        $filename = ucfirst(strtolower($type)) . '-' . $this->data['partner_order']->code() . '.pdf';
        $file = $this->getTempFolder() . $filename;
        $path = public_path('temp') . '/' . $file;
        $this->pdf->save($path);
        if ($mPdf) {
            $mPDF=$this->getMpdf();
            $mPDF->simpleTables = true;
            $mPDF->packTableData = true;
            $mPDF->shrink_tables_to_fit = 1;
            $data = view($this->viewFileName, $this->data)->render();
            $mPDF->WriteHTML("$data", HTMLParserMode::DEFAULT_MODE);
            $folder = 'invoices/pdf/';
            $mPDF->Output($filename, "F");
            $cdn = $this->saveFileToCDN($path, $folder, $file);
            File::delete($file);
            return [
                'link' => $cdn
            ];
        }
    }

    private function getTempFolder()
    {
        $temp_folder = public_path() . '/';
        if (!is_dir($temp_folder)) {
            mkdir($temp_folder, 0777, true);
        }
        return $temp_folder;
    }

    private function saveToCDN($file, $filename)
    {
        $s3_invoice_path = 'invoices/';
        return $this->saveFileToCDN($file, $s3_invoice_path, $filename);
    }

    protected function getViewPath()
    {
        return $this->viewPath ?: "reports.pdfs.";
    }
}
