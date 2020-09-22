<?php namespace Sheba\PartnerOrder;

//use App;
use App\Models\PartnerOrder;
use Illuminate\Support\Facades\App;
use Sheba\FileManagers\CdnFileManager;
use Sheba\Repositories\PartnerOrderRepository;

class InvoiceHandler
{
    use CdnFileManager;

    private $partnerOrder;

    function __construct(PartnerOrder $partner_order)
    {
        $this->partnerOrder = $partner_order->calculate(true);
    }

    public function save($type)
    {
        $filename = ucfirst(strtolower($type)) . '-' . $this->partnerOrder->code() . '.pdf';
        $file = $this->getTempFolder() . $filename;
        $partner_order = $this->partnerOrder;
        App::make('dompdf.wrapper')->loadView('pdfs.invoice', compact('partner_order', 'type'))->save($file);
        $s3_invoice_link = $this->saveToCDN($file, $filename);

        return [
            'link' => $s3_invoice_link
        ];
    }

    private function saveToCDN($file, $filename)
    {
        $s3_invoice_path = 'invoices/';
        return $this->saveFileToCDN($file, $s3_invoice_path, $filename);
    }

    private function getTempFolder()
    {
        $temp_folder = public_path() . '/uploads/invoices/';
        if (!is_dir($temp_folder)) {
            mkdir($temp_folder, 0777, true);
        }
        return $temp_folder;
    }
}