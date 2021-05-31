<?php namespace Sheba\Reports\Pos;

use Sheba\Reports\Pos\Sales\CustomerWise;
use Sheba\Reports\Pos\Sales\ProductWise;
use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class PosReportRepository extends BaseRepository
{
    /** @var ProductWise $productWise */
    private $productWise;
    /** @var CustomerWise $customerWise */
    private $customerWise;
    private $api;

    /**
     * PosReportRepository constructor.
     * @param ProductWise $productWise
     * @param CustomerWise $customerWise
     */
    public function __construct(ProductWise $productWise, CustomerWise $customerWise, AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/reports/';
        $this->productWise = $productWise;
        $this->customerWise = $customerWise;
    }

    /**
     * @return ProductWise
     */
    public function getProductWise()
    {
        return $this->productWise;
    }

    /**
     * @return CustomerWise
     */
    public function getCustomerWise()
    {
        return $this->customerWise;
    }

    public function getJournalReport($userId, $startDate, $endDate, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'journal_report_data?start_date=' . strtotime($startDate) . "&end_date=" . strtotime($endDate) );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getProfitLossReport($userId, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'profit_loss_report' );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }

    public function getDetailsLedgerReport($userId, $startDate, $endDate, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . 'details_ledger_report?start_date=' . strtotime($startDate) . "&end_date=" . strtotime($endDate) );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}
