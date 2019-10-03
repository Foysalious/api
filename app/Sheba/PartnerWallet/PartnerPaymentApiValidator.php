<?php namespace Sheba\PartnerWallet;

use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use Sheba\Repositories\PartnerTransactionRepository;

class PartnerPaymentApiValidator
{
    use ModificationFields;

    public function hasError(Request $request)
    {
        if(!$resource = Resource::find($request->resource_id)) return ['code' => 401, 'msg' => "Resource not found."];
        if($resource->remember_token != $request->remember_token) return ['code' => 401, 'msg' => "Token does not match."];
        if(!$this->resourceCanSendMoney($resource, $request->partner_id)) return ['code' => 403, 'msg' => "Resource can't do this."];
        if(!$this->wasMoneyActuallyReceived($request->payment_token, $request->partner_id)) return ['code' => 403, 'msg' => "Fraudulent request."];
        if($this->isTransactionAlreadyExist($request->transaction_details)) return ['code' => 403, 'msg' => "Fraudulent request with once valid transaction."];
        if($request->amount < 1) return ['code' => 400, 'msg' => "Invalid amount."];
        $this->setModifier($resource);
        return false;
    }

    private function resourceCanSendMoney(Resource $resource, $partner_id)
    {
        return $resource->isManager(Partner::find($partner_id));
    }

    private function wasMoneyActuallyReceived($payment_token, $partner_id)
    {
        $cache_name = "partner_" . $partner_id . "_payment_reconcile_token";
        $cached_token = \Cache::store('redis')->get($cache_name);
        \Cache::store('redis')->forget($cache_name);
        return !empty($payment_token) && $cached_token == $payment_token;
    }

    private function isTransactionAlreadyExist($transaction_detail)
    {
        return (new PartnerTransactionRepository(new Partner()))->hasSameDetails($transaction_detail);
    }
}