<?php namespace Sheba\Business\Procurement;

use App\Jobs\Business\SendTenderBillInvoiceEmailToBusiness;
use App\Models\Bid;
use App\Models\Business;
use App\Models\Member;
use App\Models\Procurement;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class BillEmailToBusinessSuperAdmin
{
    /** @var BillInvoiceDataGenerator $dataGenerator */
    private $dataGenerator;
    /** @var Procurement $procurement */
    private $procurement;
    /** @var Bid $bid */
    private $bid;

    /**
     * BillEmailToBusinessSuperAdmin constructor.
     * @param BillInvoiceDataGenerator $data_generator
     */
    public function __construct(BillInvoiceDataGenerator $data_generator)
    {
        $this->dataGenerator = $data_generator;
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

        $file_name = public_path('assets/') . Carbon::now()->timestamp . "_" . $procurement_info['type'] . ".pdf";
        App::make('dompdf.wrapper')->loadView('pdfs.procurement_invoice', compact('procurement_info'))->save($file_name);

        $data = [
            'subject'=> ucwords($procurement_info['type']) . " for " . $procurement_info['code'],
            'order_id'=> $procurement_info['code'],
            'type'=> $procurement_info['type']
        ];

        foreach ($business->superAdmins as $member) {
            /** @var Member $member */
            $email = $member->profile->email;
            $data['super_admin_name'] = $member->profile->name ? ucwords($member->profile->name) : "Sir/Madam";
            if ($email) {
                (new SendTenderBillInvoiceEmailToBusiness($email, $file_name, $data))->handle();
                // $this->dispatch(new SendTenderBillInvoiceEmailToBusiness($email, $file));
            }
        }
        unlink($file_name);
    }
}
