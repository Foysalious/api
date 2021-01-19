<?php namespace Sheba\PaymentLink;

use App\Repositories\PartnerRepository;

class PaymentLink
{
    private $defaultPaymentLink;

    private $paymentLinkVideo;

    public function __construct()
    {
    }

    public function setPaymentLinkVideo($payment_link_video)
    {
        $this->paymentLinkVideo = $payment_link_video;
        return $this;
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
     * @param $partner
     * @return array|mixed|null
     */
    public function getPaymentLinkVideo($partner)
    {
        $feature_video = (new PartnerRepository($partner))->featureVideos('payment_link');
        return (isset($feature_video) && isset($feature_video[0])) ? $feature_video[0] : null;
    }

    public function dashboard()
    {
        $data = [
            "default_payment_link"           => $this->defaultPaymentLink,
            "payment_link_video"             => $this->paymentLinkVideo,
            "faq_page"                       => PaymentLinkStatics::faq_webview(),
            "transaction_message"            => PaymentLinkStatics::get_transaction_message(),
            "payment_link_tax"               => PaymentLinkStatics::get_payment_link_tax(),
            "payment_link_charge_percentage" => PaymentLinkStatics::get_payment_link_commission()
        ];
//        $data = array_merge($data, []);
        return $data;
    }
}
