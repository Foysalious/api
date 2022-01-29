<?php namespace Sheba\PaymentLink;

use App\Http\Controllers\TrainingVideoController;
use Sheba\Dal\TrainingVideo\Contract as TrainingVideoRepository;
use Sheba\ExternalPaymentLink\ExternalPayments;

class PaymentLink
{
    private $defaultPaymentLink;

    private $trainingVideoRepo;

    public function __construct(TrainingVideoRepository $contract)
    {
        $this->trainingVideoRepo = $contract;
    }

    /**
     * @param $default_payment_link
     * @param int $array
     * @return array
     */
    public function defaultPaymentLinkData($default_payment_link, $array = 1)
    {
        $this->defaultPaymentLink = $array ? [
            'link_id' => $default_payment_link[0]['linkId'],
            'link'    => $default_payment_link[0]['link'],
            'amount'  => $default_payment_link[0]['amount']
        ] : [
            'link_id' => $default_payment_link->linkId,
            'link'    => $default_payment_link->link,
            'amount'  => $default_payment_link->amount
        ];
        return $this->defaultPaymentLink;
    }

    /**
     * @return array|mixed|null
     */
    public function getPaymentLinkVideo()
    {
        $data = $this->trainingVideoRepo->getByScreen('payment_link');
        return (new TrainingVideoController())->formatResponse($data);
    }

    public function dashboard($partner): array
    {
        return array_merge($this->dashboardData(), $this->getInactiveGatewayMessage($partner));
    }

    private function dashboardData(): array
    {
        return [
            "default_payment_link"           => $this->defaultPaymentLink,
            "payment_link_video"             => $this->getPaymentLinkVideo(),
            "faq_page"                       => PaymentLinkStatics::faq_webview(),
            "transaction_message"            => PaymentLinkStatics::get_transaction_message(),
            "payment_link_tax"               => PaymentLinkStatics::get_payment_link_tax(),
            "payment_link_charge_percentage" => PaymentLinkStatics::get_payment_link_commission()
        ];
    }

    private function getInactiveGatewayMessage($partner): array
    {
        return [
            "inactive_digital_collection" => (new ExternalPayments())->getPaymentLinkStatus($partner) ? null : PaymentLinkStatics::INACTIVE_DIGITAL_COLLECTION,
            "inactive_emi_gateway"        => (new ExternalPayments())->getPaymentLinkStatus($partner, 1) ? null : PaymentLinkStatics::INACTIVE_EMI_PAYMENT
        ];
    }
}
