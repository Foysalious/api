<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Sheba\AccountingEntry\Repository\AccountingDueTrackerRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\DueTrackerRepository;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\Repositories\BaseRepository;

class PosCustomerRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return PosCustomer
     */
    public function save(array $data)
    {
        return PosCustomer::create($this->withCreateModificationField($data));
    }

    /**
     * @param Partner $partner
     * @param $customerId
     * @param $request
     * @return int[]
     * @throws AccountingEntryServerError
     * @throws InvalidPartnerPosCustomer
     * @throws \Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError
     */
    public function getDueAmountFromDueTracker(Partner $partner, $customerId, $request)
    {
        $response = [
            'due' => 0,
            'payable' => 0
        ];
        $request->merge(['customer_id' => $customerId]);
        /** @var AccountingDueTrackerRepository $accDueTrackerRepository */
        $accDueTrackerRepository = app(AccountingDueTrackerRepository::class);
        // checking the partner is migrated to accounting
        if ($accDueTrackerRepository->isMigratedToAccounting($partner->id)) {
            $data = $accDueTrackerRepository->setPartner($partner)->dueListBalanceByCustomer($customerId);
        } else {
            /** @var DueTrackerRepository $dueTrackerRepo */
            $dueTrackerRepo = app(DueTrackerRepository::class);
            $data = $dueTrackerRepo->getDueListByProfile($partner, $request);
        }
        if ($data['balance']['type'] == 'receivable') {
            $response['due'] = $data['balance']['amount'];
        }
        else {
            $response['payable'] = $data['balance']['amount'];
        }
        return $response;
    }
}