<?php namespace Sheba\Vendor\Voucher;

use App\Models\Customer;
use App\Models\Tag;
use App\Repositories\VoucherRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VendorVoucherDataGenerator
{
    private $data;
    private $voucherRepository;
    private $vendor;
    private $vendor_channels = [
        'xtra' => 'xtra'
    ];

    /**
     * @param mixed $data
     * @return VendorVoucherDataGenerator
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param VoucherRepository $voucherRepository
     * @return VendorVoucherDataGenerator
     */
    public function setRepository(VoucherRepository $voucherRepository)
    {
        $this->voucherRepository = $voucherRepository;
        return $this;
    }

    public function setChannel($channel)
    {
        if($channel === $this->vendor_channels['xtra']) $this->vendor = (new XtraVoucher())->getVendor();

        return $this;
    }

    private function generateRules()
    {
        return [
            'mobile' => '+88' . $this->data->mobile,
            'sales_channels' => config('vendor.vendor_promo_applicable_sales_channels'),
            "applicant_types" => ["customer"]
        ];
    }

    private function generateCode()
    {
        $customer = Customer::whereHas('profile', function ($query) {
            return $query->where('mobile', '+88' . $this->data->mobile);
        })->first();

        return strtoupper(($this->data->channel ? $this->data->channel : 'PROMO')
            .($customer ? explode(' ',trim($customer->getName()))[0] : $this->data->mobile)
            .$this->data->amount.$this->generateRandomString(4));
    }

    private function generateRandomString($length = 10) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function buildVendorVoucherData()
    {
        return [
            'code' => $this->generateCode(),
            'start_date' => Carbon::parse($this->data->start_date)->format('Y-m-d h:i:s'),
            'end_date' => Carbon::parse($this->data->end_date)->format('Y-m-d h:i:s'),
            'amount' => $this->data->amount,
            'is_amount_percentage' => $this->data->is_percentage,
            'cap' => $this->data->cap,
            'rules' => json_encode($this->generateRules()),
            'title' => $this->data->title ?: $this->vendor->default_title,
            'max_order' => 1,
            'max_customer' => 1,
            'is_created_by_sheba' => 1,
            'sheba_contribution' => $this->vendor->sheba_contribution,
            'vendor_contribution' => $this->vendor->vendor_contribution ,
            'owner_type' => 'App\Models\Vendor',
            'owner_id' => $this->vendor->id,
            'created_by' => $this->data->channel ? ucwords($this->data->channel) : ''
        ];
    }

    public function generate()
    {
        $voucher = $this->voucherRepository->create($this->buildVendorVoucherData());
        $voucher->tags()->save($this->vendor->default_tag);
        return $voucher;
    }
}
