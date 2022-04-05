<?php

namespace Sheba\OrderAdvanceWithdrawalRequest;

use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\OrderAdvanceWithdrawal\OrderAdvanceWithdrawalRequestRepositoryInterface;

class OrderAdvanceWithdrawalRequestService
{
    private $readableStatus = [
        'approval_pending' => 'Approval Pending',
    ];
    private $statusColors = [
        'pending' => '#D2762A',
        'approval_pending' => '#E5AB14',
        'approved' => '#1969E1',
        'rejected' => '#000000',
        'completed' => '#0FBD20',
        'failed' => '#F31515',
        'expired' => '#B609F2',
        'cancelled' => '#DB29BE',
    ];
    private $defaultColor = '#000000';

    private $orderAdvanceWithdrawalRequestRepository;

    public function __construct(OrderAdvanceWithdrawalRequestRepositoryInterface $orderAdvanceWithdrawalRequestRepository)
    {
        $this->orderAdvanceWithdrawalRequestRepository = $orderAdvanceWithdrawalRequestRepository;
    }

    public function getMergedWithdrawalRequests($partner_order)
    {
        $withdrawal_requests = $this->orderAdvanceWithdrawalRequestRepository->getWithdrawalRequests($partner_order);
        foreach ($withdrawal_requests as $withdrawal_request) {
            if ($withdrawal_request->status === 'approved') {
                $withdrawal_request->status = $withdrawal_request->withdrawalRequest->status;
                $withdrawal_request->reject_reason = $withdrawal_request->withdrawalRequest->reject_reason;
            }
            $withdrawal_request->readable_status = $this->readableStatus[$withdrawal_request->status] ?? ucwords($withdrawal_request->status);
            $withdrawal_request->color = $this->statusColors[$withdrawal_request->status] ?? $this->defaultColor;
        }
        return $withdrawal_requests;
    }

    public function getWithdrawableAmountForPartnerOrder($partner_order)
    {
        if (!$partner_order->order->is_credit_limit_adjustable || $partner_order->sheba_collection <= 0)  return 0;

        $activeWithdrawalAmount = $this->activeRequestAgainstPartnerOrderAmount($partner_order);
        $activeWithdrawalAmount += $this->orderAdvanceWithdrawalRequestRepository->getTotalPendingAmountForPartnerOrder($partner_order);

        if ($partner_order->sheba_collection > $activeWithdrawalAmount) {
            return $partner_order->sheba_collection - $activeWithdrawalAmount;
        }
        return 0;
    }

    public function doesExceedWithdrawalAmountForOrder($amount, $partnerOrder): bool
    {
        $minAmount = min([$partnerOrder->sheba_collection, $partnerOrder->grossAmountWithLogistic]);
        return $partnerOrder->sheba_collection == 0 || (($this->activeRequestAgainstPartnerOrderAmount($partnerOrder) + $amount) > $minAmount);
    }

    public function activeRequestAgainstPartnerOrderAmount($partner_order)
    {
        $withdrawalRequest = WithdrawalRequest::select(DB::raw('sum(amount) as total_amount'))
            ->active()
            ->where('order_id', $partner_order->order_id)
            ->where('requester_type', 'partner')
            ->where('requester_id', $partner_order->partner_id)
            ->first();

        $totalAmount = $withdrawalRequest->total_amount ?? 0;
        $totalAmount += $this->orderAdvanceWithdrawalRequestRepository->getTotalPendingAmountForPartnerOrder($partner_order);
        return $totalAmount;
    }
}