<?php


namespace Sheba\ExternalPaymentLink;

use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PaymentClientAuthentication\Model as PaymentClientAuthentication;
use Sheba\ExternalPaymentLink\Exceptions\InvalidEmiMonthException;
use Sheba\ExternalPaymentLink\Exceptions\InvalidTransactionIDException;
use Sheba\ExternalPaymentLink\Exceptions\PaymentLinkInitiateException;
use Sheba\ExternalPaymentLink\Exceptions\TransactionIDNotFoundException;
use Sheba\ExternalPaymentLink\Statics\ExternalPaymentStatics;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\Pos\Repositories\PartnerPosCustomerRepository;
use Sheba\Pos\Repositories\PosCustomerRepository;
use Sheba\Repositories\ProfileRepository;
use Sheba\RequestIdentification;
use Sheba\PaymentLink\Creator;

class ExternalPayments
{
    use ModificationFields;

    /** @var Creator $creator */
    private $creator;
    /** @var ProfileRepository $profileRepo */
    private $profileRepo;
    /** @var PosCustomerRepository $posCustomerRepo */
    private $posCustomerRepo;
    /** @var PartnerPosCustomerRepository $partnerPosCustomerRepo */
    private $partnerPosCustomerRepo;
    /** @var PaymentClientAuthentication $client */
    private $client;
    private $transactionID;
    private $data;
    private $agent;

    public function __construct()
    {
        /** @var Creator creator */
        $this->creator                = app(Creator::class);
        $this->profileRepo            = app(ProfileRepository::class);
        $this->posCustomerRepo        = app(PosCustomerRepository::class);
        $this->partnerPosCustomerRepo = app(PartnerPosCustomerRepository::class);
    }

    /**
     * @param Request $request
     * @return ExternalPayments
     * @throws InvalidTransactionIDException
     */
    public function setData(Request $request)
    {
        $this->data = $request->only(ExternalPaymentStatics::dataFields());
        $this->setAgent();
        $this->data['emi_month'] = (int)$request->get('emi_month');
        if (!isset($this->data['transaction_id'])) throw new InvalidTransactionIDException();
        $this->setTransactionID($this->data['transaction_id']);
        return $this;
    }

    private function setAgent()
    {
        $this->data = array_merge($this->data, (new RequestIdentification())->get());
        return $this;
    }

    /**
     * @param PaymentClientAuthentication $client
     * @return ExternalPayments
     */
    public function setClient(PaymentClientAuthentication $client)
    {
        $this->client = $client;
        $this->setModifier($this->client->partner);
        return $this;
    }

    /**
     * @param mixed $transactionID
     * @return ExternalPayments
     */
    private function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
        return $this;
    }

    /**
     * @return $this
     * @throws InvalidTransactionIDException
     */
    public function beforeCreateValidate()
    {
        $already = $this->client->payments()->byTransactionID($this->transactionID)->first();
        if (!empty($already)) throw new InvalidTransactionIDException();
        return $this;
    }

    /**
     * @param $transaction_id
     * @return mixed
     * @throws TransactionIDNotFoundException
     */
    public function getPaymentDetails($transaction_id)
    {
        $this->setTransactionID($transaction_id);
        $payment = $this->client->payments()->byTransactionID($this->transactionID)->first();
        if (empty($payment)) throw new TransactionIDNotFoundException();
        return $this->formatData($payment);
    }

    private function formatData($external_payment)
    {
        return [
            "id"                          => $external_payment->id,
            "amount"                      => $external_payment->amount,
            "success_url"                 => $external_payment->success_url,
            "fail_url"                    => $external_payment->fail_url,
            "customer_mobile"             => $external_payment->customer_mobile,
            "customer_name"               => $external_payment->customer_name,
            "emi_month"                   => $external_payment->emi_month,
            "partner_id"                  => $external_payment->partner_id,
            "purpose"                     => $external_payment->purpose,
            "client_id"                   => $external_payment->client_id,
            "client_name"                 => $this->client->name,
            "transaction_id"              => $external_payment->transaction_id,
            "created_at"                  => Carbon::parse($external_payment->created_at)->toDateTimeString(),
            "created_by"                  => $external_payment->created_by_name,
            "payment_id"                  => $external_payment->payment_id,
            "payment_status"              => $external_payment->payment ? $external_payment->payment->status : "pending",
            "payment_transaction_details" => $external_payment->payment ? json_decode($external_payment->payment->transaction_details) : null,
            "payment_from_ip"             => $external_payment->payment ? $external_payment->payment->ip : null,
            "payment_at"                  => $external_payment->payment ? Carbon::parse($external_payment->payment->created_at)->toDateTimeString() : null,
            "invoice_link"                => $external_payment->payment ? $external_payment->payment->invoice_link : null
        ];
    }

    /**
     */
    public function create()
    {
        $response = null;
        DB::transaction(function () use (&$response) {
            $this->processData();
            $payment  = $this->createPaymentRequest();
            $response = $this->createPaymentLink($payment);
        });
        return $response;

    }

    private function processData()
    {
        $this->data['customer_mobile'] = $this->data['customer_mobile'] ? BDMobileFormatter::format($this->data['customer_mobile']) : '';
        $this->data['customer_name']   = $this->data['customer_name'] ?: '';
        $this->data['purpose']         = ExternalPaymentStatics::getPurpose($this->data, $this->client);
    }

    private function createPaymentRequest()
    {

        $data               = $this->data;
        $data['partner_id'] = $this->client->partner->id;
        return $this->client->payments()->create($this->withCreateModificationField($data));
    }

    /**
     * @param $payment
     * @return array
     * @throws InvalidEmiMonthException|PaymentLinkInitiateException
     */
    private function createPaymentLink($payment)
    {
        $emi_month_invalid = Creator::validateEmiMonth($this->data);
        if ($emi_month_invalid !== false) throw new InvalidEmiMonthException($emi_month_invalid);
        $this->creator->setIsDefault(0)->setAmount($this->data['amount'])->setReason($this->data['purpose'])->setUserName($this->client->partner->name)->setUserId($this->client->partner->id)->setUserType('partner')->setTargetId($payment->id)->setTargetType('external_payment')->setEmiMonth((int)$this->data['emi_month'])->setEmiCalculations();
        $this->setCustomer();
        $external_link = $this->creator->save();
        if (!$external_link) throw new PaymentLinkInitiateException();
        return $this->creator->getPaymentLinkData();


    }

    private function setCustomer()
    {
        if (!empty($this->data['customer_mobile'])) {

            $posCustomer = PosCustomer::query()->getByMobile($this->data['customer_mobile']);
            if (!$posCustomer) {
                $posCustomer = $this->createPosCustomer();
            }
            $pPosCustomer = PartnerPosCustomer::where('partner_id', $this->client->partner->id)->where('customer_id', $posCustomer->id)->first();
            if (!$pPosCustomer) {
                $this->partnerPosCustomerRepo->save(['partner_id' => $this->client->partner->id, 'customer_id' => $posCustomer->id, 'nick_name' => $this->data['customer_name']] + (new RequestIdentification())->get());
            }
            $this->creator->setPayerId($posCustomer->id)->setPayerType('pos_customer');

        }
    }

    private function createPosCustomer()
    {
        $profile = Profile::where('mobile', $this->data['customer_mobile']);
        if (!$profile) {
            $profile = $this->profileRepo->store($this->withCreateModificationField(['mobile' => $this->data['customer_mobile'], 'name' => isset($this->data['customer_name']) ? $this->data['customer_name'] : '']));
        }
        return $this->posCustomerRepo->save(['profile_id' => $profile->id]);

    }
}
