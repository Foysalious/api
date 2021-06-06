<?php namespace Sheba\Reports\Accounting;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;

class AccountingReportRepository extends BaseRepository
{
    private $api;

    /**
     * AccountingReportRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/reports/';
    }

    public function getAccountingReport($reportType, $userId, $startDate, $endDate, $userType = UserType::PARTNER)
    {
        try {
            return $this->client->setUserType($userType)->setUserId($userId)->setReportType($reportType)
                ->get($this->api . 'accounting-report?start_date=' . strtotime($startDate) . "&end_date=" . strtotime($endDate) );
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }
}