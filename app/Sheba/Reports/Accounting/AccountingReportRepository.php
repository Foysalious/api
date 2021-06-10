<?php namespace Sheba\Reports\Accounting;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use Sheba\Helpers\ConstGetter;

class AccountingReportRepository extends BaseRepository
{
    private $api;

    const PROFIT_LOSS_REPORT = 'profit_loss_report';
    const JOURNAL_REPORT = 'journal_report';
    const BALANCE_SHEET_REPORT = 'balance_sheet_report';
    const GENERAL_LEDGER_REPORT = 'general_ledger_report';
    const DETAILS_LEDGER_REPORT = 'details_ledger_report';
    const GENERAL_ACCOUNTING_REPORT = 'general_accounting_report';


    /**
     * AccountingReportRepository constructor.
     * @param AccountingEntryClient $client
     */
    public function __construct(AccountingEntryClient $client)
    {
        parent::__construct($client);
        $this->api = 'api/reports/';
    }

    /**
     * @param $reportType
     * @param $userId
     * @param $startDate
     * @param $endDate
     * @param $accountId
     * @param $accountType
     * @param string $userType
     * @return array
     * @throws AccountingEntryServerError
     */
    public function getAccountingReport($reportType, $userId, $startDate, $endDate, $accountId, $accountType, $userType = UserType::PARTNER): array
    {
        try {
            $data = $this->client->setUserType($userType)->setUserId($userId)
                ->get($this->api . "accounting-report/$reportType?start_date=$startDate&end_date=$endDate&account_id=$accountId&account_type=$accountType" );
            return $reportType === self::PROFIT_LOSS_REPORT ? (new ProfitLossReportData())->format_data($data): $data;
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }


    public function getAccountingReportsList(): array
    {
        return [
            [
                'key' => self::PROFIT_LOSS_REPORT,
                'report_bangla_name' => 'লাভ-ক্ষতি রিপোর্ট'
            ],
            [
                'key' => self::JOURNAL_REPORT,
                'report_bangla_name' => 'জার্নাল রিপোর্ট'
            ],
            [
                'key' => self::BALANCE_SHEET_REPORT,
                'report_bangla_name' => 'ব্যাল্যান্স শিট রিপোর্ট'
            ],
            [
                'key' => self::GENERAL_LEDGER_REPORT,
                'report_bangla_name' => 'জেনারেল লেজার রিপোর্ট'
            ],
            [
                'key' => self::DETAILS_LEDGER_REPORT,
                'report_bangla_name' => 'বিস্তারিত লেজার রিপোর্ট'
            ],
            [
                'key' => self::GENERAL_ACCOUNTING_REPORT,
                'report_bangla_name' => 'সাধারণ হিসাব রিপোর্ট'
            ]
        ];
    }
}