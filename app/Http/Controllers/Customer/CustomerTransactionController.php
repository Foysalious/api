<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\GiftCardPurchase;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class CustomerTransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'sometimes|required|in:credit,debit'
            ]);
            list($offset, $limit) = calculatePagination($request);
            /** @var Customer $customer */
            $customer = $request->customer;
            $transactions = $customer->transactions();
            $bonus_logs = $customer->bonusLogs();
            if ($request->filled('type')) {
                $transactions->where('type', ucwords($request->type));
                $bonus_logs->where('type', ucwords($request->type));
            }
            $transactions = $transactions->select('id', 'customer_id', 'type', 'amount', 'log', 'created_at', 'partner_order_id', 'created_at')
                ->with('partnerOrder.order')->orderBy('id', 'desc')->get();
            $bonus_logs = $bonus_logs->with('spentOn')->get();
            $transactions->each(function ($transaction) {
                $transaction['valid_till'] = null;
                if ($transaction->partnerOrder) {
                    $transaction['category_name'] = $transaction->partnerOrder->jobs->first()->category->name;
                    $transaction['log'] = $transaction['category_name'];
                    $transaction['transaction_type'] = "Service Purchase";
                    $transaction['order_code'] = $transaction->partnerOrder->order->code();
                } else {
                    $transaction['category_name'] = $transaction['transaction_type'] = $transaction['order_code'] = "";
                    $transaction['transaction_type'] = $transaction['log'];
                }
                removeRelationsAndFields($transaction);
            });

            foreach ($bonus_logs as $bonus_log) {
                if ($bonus_log->type == 'Credit') $transactions = $this->formatCreditBonusTransaction($bonus_log, $transactions);
                else $transactions = $this->formatDebitBonusTransaction($bonus_log, $transactions);
            }
            $transactions = $transactions->sortByDesc('created_at')->splice($offset, $limit)->values()->all();
            $gift_cards_purchased = GiftCardPurchase::where('customer_id', $customer->id)
                ->where('status', 'successful')
                ->where('valid_till', '>=', Carbon::now())
                ->min('valid_till');
            $warning_message = $this->generateWarningMessageV2($gift_cards_purchased);
            return api_response($request, $transactions, 200, [
                'transactions' => $transactions, 'balance' => $customer->shebaCredit(),
                'credit' => round($customer->shebaCredit(), 2), 'bonus' => round($customer->shebaBonusCredit(), 2),
                'warning_message' => $warning_message
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function generateWarningMessageV2($gift_cards_purchased)
    {
        $warning_message = null;
        if ($gift_cards_purchased)
            $warning_message = 'Sheba Credit will expire on ' . Carbon::parse($gift_cards_purchased)->format('M d, Y');
        return $warning_message;
    }

    private function generateWarningMessage($bonus_logs, $gift_cards_purchased)
    {
        $warning_message = null;
        $valid_bonus_logs = $bonus_logs->where('valid_till', '>=', Carbon::now()->toDateTimeString());

        $gift_cards = GiftCard::whereIn('id', $gift_cards_purchased)->orderBy('end_date', 'desc')->get();

        $gift_card_end_date = collect();
        foreach ($gift_cards as $gift_card) {
            $gift_card_end_date->push($gift_card->end_date);
        }
        $gift_card_with_min_end_date = $gift_cards->filter(function ($gift_card) use ($gift_card_end_date) {
            return $gift_card->end_date == $gift_card_end_date->min();
        });

        if (count($valid_bonus_logs) > 0) {
            if (count($gift_cards) > 0) {
                if (Carbon::parse($valid_bonus_logs[0]->valid_till)->gt(Carbon::parse($gift_cards[0]->end_date))) {
                    $warning_message = ((int)$gift_cards[0]->credit) . ' Sheba Credit will expire on ' . Carbon::parse($gift_cards[0]->end_date)->format('M d, Y');
                } else
                    $warning_message = ((int)$valid_bonus_logs[0]->amount) . ' Sheba Credit will expire on ' . Carbon::parse($valid_bonus_logs[0]->valid_till)->format('M d, Y');
            } else
                $warning_message = ((int)$valid_bonus_logs[0]->amount) . ' Sheba Credit will expire on ' . Carbon::parse($valid_bonus_logs[0]->valid_till)->format('M d, Y');
        } else {
            if (count($gift_cards) > 0) {
                $warning_message = ((int)$gift_card_with_min_end_date->first()->credit) . ' Sheba Credit will expire on ' . Carbon::parse($gift_card_with_min_end_date->first()->end_date)->format('M d, Y');
            }
        }
        return $warning_message;
    }

    private function formatDebitBonusTransaction($bonus, $transactions)
    {
        $spent_on = $bonus->spent_on;
        $category = null;
        if ($spent_on instanceof PartnerOrder) {
            $category = $spent_on->jobs->first()->category;
            $log = $bonus->log ? $bonus->log : 'Service Purchased ' . $category->name;
        } elseif ($spent_on) {
            $log = 'Purchased ' . class_basename($spent_on);
        } else {
            $log = 'Bonus credit expired';
        }
        $is_spend_on_order = $spent_on && ($spent_on instanceof PartnerOrder);
        $category = $is_spend_on_order ? $bonus->spent_on->jobs->first()->category : null;

        $transactions->push([
            'id'            => $bonus->id,
            'customer_id'   => $bonus->user_id,
            'type'          => 'Debit',
            'amount'        => $bonus->amount,
            'log'           => $log,
            'created_at'    => $bonus->created_at->toDateTimeString(),
            'valid_till'    => null,
            'order_code'    => $is_spend_on_order ? $spent_on->order->code() : '',
            'category_name' => $category ? $category->name : '',
            'partner_order_id' => $bonus->spent_on_id,
            'transaction_type' => $category ? 'Service Purchase' : ''
        ]);

        return $transactions;
    }

    private function formatCreditBonusTransaction($bonus, $transactions)
    {
        $transactions->push(array(
            'id' => $bonus->id,
            'customer_id' => $bonus->user_id,
            'type' => 'Credit',
            'amount' => $bonus->amount,
            'log' => $bonus->log,
            'created_at' => $bonus->created_at->toDateTimeString(),
            'partner_order_id' => null,
            'valid_till' => $bonus->valid_till->format('d/m/Y'),
            'order_code' => '',
            'transaction_type' => $bonus->log,
            'category_name' => '',
        ));
        return $transactions;
    }
}
