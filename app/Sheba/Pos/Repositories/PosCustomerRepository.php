<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Sheba\AccountingEntry\Repository\AccountingDueTrackerRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
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
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    public function getDueAmountFromDueTracker(Partner $partner, $customerId): array
    {
        $response = [
            'due' => 0,
            'payable' => 0
        ];
        /** @var AccountingDueTrackerRepository $accDueTrackerRepository */
        $accDueTrackerRepository = app(AccountingDueTrackerRepository::class);
        $data = $accDueTrackerRepository->setPartner($partner)->dueListBalanceByCustomer($customerId);
        if ($data['balance']['type'] == 'receivable') {
            $response['due'] = $data['balance']['amount'];
        }
        else {
            $response['payable'] = $data['balance']['amount'];
        }
        return $response;
    }

    public function deleteCustomerFromDueTracker(Partner $partner, $customerId)
    {
        /** @var AccountingDueTrackerRepository $accDueTrackerRepository */
        $accDueTrackerRepository = app(AccountingDueTrackerRepository::class);
        $accDueTrackerRepository->setPartner($partner)->deleteCustomer($customerId);

    }
}