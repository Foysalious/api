<?php

namespace Sheba\OrderAdvanceWithdrawalRequest;

use Sheba\Dal\OrderAdvanceWithdrawal\OrderAdvanceWithdrawalRequestRepositoryInterface;

class OrderAdvanceWithdrawalRequestService
{
    private $readableStatus = [
        'approval_pending' => 'Approval Pending',
    ];
    private $statusColors = [
        'pending' => '#0FBD20',
        'approval_pending' => '#0FBD20',
        'approved' => '#0FBD20',
        'rejected' => '#0FBD20',
        'completed' => '#0FBD20',
        'failed' => '#0FBD20',
        'expired' => '#0FBD20',
        'cancelled' => '#0FBD20',
    ];
    private $defaultColor = '#0FBD21';

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
}