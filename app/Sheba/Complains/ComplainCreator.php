<?php namespace Sheba\Complains;

use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Profile;
use Carbon\Carbon;

use Sheba\Dal\Complain\EloquentImplementation as ComplainRepo;
use Sheba\Dal\Accessor\EloquentImplementation as AccessorRepo;
use Sheba\Dal\ComplainPreset\EloquentImplementation as ComplainPresetRepo;
use Sheba\ModificationFields;
use Sheba\Notification\ComplainNotification;

class ComplainCreator
{
    use ModificationFields;

    private $accessorRepo;
    private $presetRepo;
    private $complainRepo;

    private $data;

    public function __construct(ComplainRepo $complain, AccessorRepo $accessor_repo, ComplainPresetRepo $complain_preset_repo)
    {
        $this->complainRepo = $complain;
        $this->accessorRepo = $accessor_repo;
        $this->presetRepo = $complain_preset_repo;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function hasError()
    {
        $accessor = $this->accessorRepo->find($this->data['accessor_id'])->name;
        if(empty($this->data['partner_id']) && empty($this->data['order_id']) && empty($this->data['customer_mobile']))
            return 'Please Provide Partner Or Customer Information';
        if($accessor == "Customer" && empty($this->data['customer_mobile']) && empty($this->data['order_id']))
            return 'Please Provide Customer\'s Information';
        if($accessor == "Partner" && empty($this->data['partner_id']) && empty($this->data['order_id']))
            return 'Please Provide Partner\'s Information';

        if (!empty($this->data['customer_mobile'])) {
            $profile = Profile::where('mobile', formatMobile($this->data['customer_mobile']))->first();
            $customer = $profile ? $profile->customer : null;
            if(!$customer)
                return 'Customer Not Found';
            $this->data['customer_id'] = $customer->id;
        }
        if(!empty($this->data['order_id'])) {
            $partner_order = Order::find($this->data['order_id'])->partnerOrders->filter(function (PartnerOrder $p_o) {
                return $p_o->getStatus() != 'Cancelled';
            })->first() ? : Order::find($this->data['order_id'])->partnerOrders->last();

            $job = $partner_order->jobs->filter(function ($job) {
                return $job->status != 'Cancelled';
            })->first() ? : $partner_order->jobs->last();

            $this->data['job_id'] = $job->id;
            $partner_id = $partner_order->partner_id;
            $customer_id = $partner_order->order->customer_id;
            if(!empty($this->data['partner_id']) && ($partner_id != $this->data['partner_id'])) {
                return 'Partner doesn\'t match';
            } else {
                $this->data['partner_id'] = $partner_id;
            }

            if(!empty($this->data['customer_id']) && ($customer_id != $this->data['customer_id'])) {
                return 'Customer doesn\'t match';
            } else {
                $this->data['customer_id'] = $customer_id;
            }
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        $data = $this->processData();
        $complain = $this->complainRepo->create($this->withBothModificationFields($data));
        (new ComplainNotification($complain))->notifyOnCreate();
        return $complain;
    }

    private function processData()
    {
        $preset_id = (int) $this->data['complain_preset'];
        $preset = $this->presetRepo->find($preset_id);
        $follow_up_time = Carbon::now()->addMinutes($preset->complainType->sla);

        return [
            'complain'           => $this->data['complain'],
            'complain_preset_id' => $preset_id,
            'source'             => $this->data['complain_source'],
            'follow_up_time'     => $follow_up_time,
            'accessor_id'        => $this->data['accessor_id'],
            'assigned_to_id'     => empty($this->data['assigned_to_id'])? null : (int) $this->data['assigned_to_id'],
            'job_id'             => empty($this->data['job_id']) ? null : $this->data['job_id'],
            'customer_id'        => isset($this->data['customer_id']) ? $this->data['customer_id']:  null,
            'partner_id'         => empty($this->data['partner_id']) ? null : $this->data['partner_id']
        ];
    }
}