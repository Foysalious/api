<?php namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\GiftCardPurchase;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Payable\GiftCardPurchaseAdapter;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Throwable;

class GiftCardController extends Controller
{
    use ModificationFields;

    public function getGiftCards(Request $request)
    {
        try {
            $gift_cards = GiftCard::valid()->get();
            foreach ($gift_cards as $gift_card) {
                $gift_card->credit = (float)$gift_card->credit;
                $gift_card->price = (float)$gift_card->price;
                $gift_card->validity = $this->getMonthDiff($gift_card->start_date, $gift_card->end_date);
                $gift_card->valid_time = Carbon::parse($gift_card->start_date)->format('d/m/Y') . '-' . Carbon::parse($gift_card->end_date)->format('d/m/Y');
                removeRelationsAndFields($gift_card);
            }
            $instructions = [
                [
                    'question' => 'Purchase any voucher',
                    'answer' => 'Purchase any voucher from available voucher list. You will be asked to pay the required amount from our available payment method. The amount of voucher will be added to Sheba bonus credit.'
                ],
                [
                    'question' => 'Voucher Validity',
                    'answer' => 'Each voucher has its own validity. You can check the validity with each voucher. The voucher amount with lower validity will be used first.'
                ],
                [
                    'question' => 'Pay with Voucher',
                    'answer' => 'You can use Sheba bonus credit in service purchase. In service purchase you can find Sheba Bonus Credit section from where you can select Sheba bonus credit for full or partial payment.'
                ],
            ];
            $data = ['gift_cards' => $gift_cards, 'instructions' => $instructions];
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param PaymentManager $payment_manager
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function purchaseGiftCard(Request $request, PaymentManager $payment_manager)
    {
        $this->validate($request, [
            'payment_method' => 'required|string|in:online,bkash,wallet,cbl',
            'gift_card_id' => 'required'
        ]);
        /** @var GiftCard $gift_card */
        $gift_card = GiftCard::find((int)$request->gift_card_id);
        if (!$gift_card)
            return api_response($request, null, 404, ['message' => 'Gift Card Not found.']);

        if (!$gift_card->isValid())
            return api_response($request, null, 403, ['message' => 'Gift card is not valid.']);

        $gift_card_purchased_order = GiftCardPurchase::create(
            $this->withCreateModificationField(
                [
                    'customer_id' => $request->customer->id,
                    'gift_card_id' => $gift_card->id,
                    'amount' => (float)$gift_card->price,
                    'credits_purchased' => (float)$gift_card->credit,
                    'status' => 'initialized',
                    'valid_till' => Carbon::now()->addMonth((int)$gift_card->validity_in_months),
                ]
            )
        );
        $gift_card_purchase_adapter = new GiftCardPurchaseAdapter();
        $payable = $gift_card_purchase_adapter->setModelForPayable($gift_card_purchased_order)->getPayable();
        $payment = $payment_manager->setMethodName($request->payment_method)->setPayable($payable)->init();
        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);

    }

    protected function getMonthDiff($start_date, $end_date)
    {
        $diff_in_months = Carbon::parse($start_date)->diffInMonths(Carbon::parse($end_date));
        if ($diff_in_months % 12 === 0)
            return ($diff_in_months / 12) . ' year';
        return $diff_in_months . ' month';
    }
}
