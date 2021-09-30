<?php namespace App\Sheba\Payment\Methods\OkWallet\Response;

use App\Models\Payment;
use App\Repositories\PaymentStatusChangeLogRepository;
use App\Sheba\Payment\Methods\OkWallet\Request\InitRequest;
use Sheba\Payment\Methods\OkWallet\OkWalletClient;
use Sheba\Payment\Methods\OkWallet\Response\ValidationResponse;
use Sheba\Payment\Statuses;

class ValidateTransaction
{
    private $paymentRepository;
    private $payment;
    private $request;

    /**
     * ValidateTransaction constructor.
     * @param PaymentStatusChangeLogRepository $payment_repository
     */
    public function __construct(PaymentStatusChangeLogRepository $payment_repository)
    {
        $this->paymentRepository = $payment_repository;
        $this->request = json_decode(request()->all()['data'],true);

    }

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        $this->paymentRepository->setPayment($this->payment);
        return $this;
    }

    /**
     * @return mixed
     * @throws \Sheba\TPProxy\TPProxyServerError
     */
    public function initValidation()
    {
        $validation_response = new ValidationResponse();
        $validation_response->setResponse($this->validateOrder((new InitRequest($this->request))->getOkTrxId()));
        $validation_response->setPayment($this->payment);

        if ($validation_response->hasSuccess()) {
            $this->changeToValidated($validation_response->getSuccess());
        } else {
            $this->changeToValidationFailed($validation_response->getError());
        }

        return $this->payment;
    }

    /**
     * @return mixed
     */
    public function changeToFailed()
    {
        $this->paymentRepository->create([
            'to' => Statuses::VALIDATION_FAILED,
            'from' => $this->payment->status,
            'transaction_details' => $this->payment->transaction_details
        ]);
        $this->payment->status = Statuses::VALIDATION_FAILED;
        $this->payment->transaction_details = json_encode($this->request);

        return $this->payment;
    }

    /**
     * @param $success
     */
    public function changeToValidated($success)
    {

        $this->paymentRepository->create([
            'to' => Statuses::VALIDATED,
            'from' => $this->payment->status,
            'transaction_details' => $this->payment->transaction_details
        ]);
        $this->payment->status = Statuses::VALIDATED;
        $this->payment->transaction_details = json_encode($success->details);
    }

    /**
     * @param $error
     */
    public function changeToValidationFailed($error)
    {
        $this->paymentRepository->create([
            'to' => Statuses::VALIDATION_FAILED,
            'from' => $this->payment->status,
            'transaction_details' => $this->payment->transaction_details
        ]);
        $this->payment->status = Statuses::VALIDATION_FAILED;
        $this->payment->transaction_details = json_encode($error->details);
    }

    /**
     * @param $transaction_id
     * @return mixed
     * @throws \Sheba\TPProxy\TPProxyServerError
     */
    private function validateOrder($transaction_id)
    {
        /** @var OkWalletClient $ok_wallet */
        $ok_wallet = app(OkWalletClient::class);
        return  $ok_wallet->validationRequest($transaction_id);
    }
}
