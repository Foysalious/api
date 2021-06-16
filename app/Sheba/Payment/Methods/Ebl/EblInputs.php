<?php


namespace Sheba\Payment\Methods\Ebl;


use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Sheba\Loan\DS\ReflectionArray;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;
use Sheba\Payment\Methods\Ebl\Stores\EblStore;

class EblInputs implements Arrayable
{
    use ReflectionArray;

    public $req_access_key;
    public $auth_amount;
    public $auth_trans_ref_no;
    public $currency;
    public $locale;
    public $profile_id;
    public $req_reference_number;
    public $signed_date_time;
    public $signed_field_names;
    public $transaction_type;
    public $req_transaction_uuid;
    public $signature;
    public $self_sign;
    public $validated;
    public $decision;





}
