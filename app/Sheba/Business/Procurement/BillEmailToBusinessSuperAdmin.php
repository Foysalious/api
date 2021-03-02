<?php namespace Sheba\Business\Procurement;

use App\Jobs\Business\SendTenderBillInvoiceEmailToBusiness;
use App\Models\Bid;
use App\Models\Business;
use App\Models\Member;
use App\Models\Procurement;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Reports\PdfHandler;

class BillEmailToBusinessSuperAdmin
{
    use DispatchesJobs;

    /** @var BillInvoiceDataGenerator $dataGenerator */
    private $dataGenerator;
    /** @var Procurement $procurement */
    private $procurement;
    /** @var Bid $bid */
    private $bid;
    /** @var PdfHandler $pdfHandler */
    private $pdfHandler;

    /**
     * BillEmailToBusinessSuperAdmin constructor.
     * @param BillInvoiceDataGenerator $data_generator
     * @param PdfHandler $pdf_handler
     */
    public function __construct(BillInvoiceDataGenerator $data_generator, PdfHandler $pdf_handler)
    {
        $this->dataGenerator = $data_generator;
        $this->pdfHandler = $pdf_handler;
    }

    /**
     * @param Procurement $procurement
     * @return $this
     */
    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        $this->bid = $procurement->getActiveBid();
        return $this;
    }

    public function send()
    {
        /** @var Business $business */
        $business = $this->procurement->owner;
        $procurement_info = $this->dataGenerator->setBusiness($business)->setProcurement($this->procurement->id)->setBid($this->bid)->get();

        $file_name = "tender_" . $this->procurement->id . "_" . $procurement_info['type'];
        $file_name = $this->pdfHandler
            ->setName($file_name)
            ->setData(['procurement_info' => $procurement_info])
            ->setViewFileWithPath('pdfs.procurement_invoice')
            ->save();

        $data = [
            'subject'=> ucwords($procurement_info['type']) . " for " . $procurement_info['code'],
            'order_id'=> $procurement_info['code'],
            'type'=> $procurement_info['type'],
            'url'=> config('sheba.business_url') . "/dashboard/orders/rfq/" . $this->procurement->id . "/bill?bidId=" . $this->bid->id
        ];

        foreach ($business->superAdmins as $member) {
            /** @var Member $member */
            $email = $member->profile->email;
            $data['super_admin_name'] = $member->profile->name ? ucwords($member->profile->name) : "Sir/Madam";

            if ($email) $this->dispatch(new SendTenderBillInvoiceEmailToBusiness($email, $file_name, $data));
        }
    }
}
