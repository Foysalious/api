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
    /** @var PDF $pdf*/
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
    public function download($mPdf=false)
    {
        $this->create();

        if ($mPdf){
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $mPDF = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    storage_path( '/fonts'),
                ]),
                'fontdata' => $fontData + [
                        'kalpurush' => [
                            'R' => 'Siyamrupali.ttf',
                            'I' => 'Siyamrupali.ttf',
                            'useOTL' => 0xFF,
                            'useKashida' => 75,
                        ]
                    ],
                'default_font' => 'kalpurush'
            ]);
            $mPDF->simpleTables=true;
            $mPDF->packTableData=true;
            $mPDF->shrink_tables_to_fit=1;
            $keep_table_proportions = TRUE;
            $data=view($this->viewFileName,$this->data)->render();
            $mPDF->SetTitle($this->filename);
            $mPDF->WriteHTML(file_get_contents(resource_path('assets/css/pdf.css')),HTMLParserMode::HEADER_CSS);
            $mPDF->WriteHTML("$data",HTMLParserMode::HTML_BODY);
            return $mPDF->Output();
        }
        return $this->pdf->download("$this->filename.$this->downloadFormat");

    }

    public function save()
    {
        $this->create();
        if (!is_dir(public_path('temp'))) {
            mkdir(public_path('temp'), 0777, true);
        }

        $folder = $this->folder ?: 'invoices/pdf/';
        $time = time();
        $file = $this->filename . "_$time." . $this->downloadFormat;
        $path = public_path('temp') . '/' . $file;
        $this->pdf->save($path);
        $cdn = $this->saveFileToCDN($path, $folder, $file);
        File::delete($path);
        return $cdn;
    }

    protected function getViewPath()
    {
        return $this->viewPath ?: "reports.pdfs.";
    }
}
