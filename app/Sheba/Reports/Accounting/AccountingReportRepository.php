<?php namespace Sheba\Reports\Accounting;

use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use App\Sheba\AccountingEntry\Repository\BaseRepository;
use App\Sheba\AccountingEntry\Constants\AccountingReport;

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
                ->get($this->api . "accounting-report/$reportType?start_date=$startDate&end_date=$endDate&account_id=$accountId&account_type=$accountType");
            if($reportType === AccountingReport::JOURNAL_REPORT) return (new JournalReportData())->format_data($data);
            return $reportType === AccountingReport::PROFIT_LOSS_REPORT ? (new ProfitLossReportData())->format_data($data): $data;
        } catch (AccountingEntryServerError $e) {
            throw new AccountingEntryServerError($e->getMessage(), $e->getCode());
        }
    }


    public function getAccountingReportsList(): array
    {
        return [
            [
                'key' => AccountingReport::PROFIT_LOSS_REPORT,
                'report_bangla_name' => 'লাভ-ক্ষতি রিপোর্ট',
                'url' => url('/v2/accounting/reports/profit_loss_report'),
                'icon' => config('accounting_entry.icon_url').'/'.'loss_profit_report.png'
            ],
            [
                'key' => AccountingReport::JOURNAL_REPORT,
                'report_bangla_name' => 'জার্নাল রিপোর্ট',
                'url' => url('/v2/accounting/reports/journal_report'),
                'icon' => config('accounting_entry.icon_url').'/'.'journal_report.png'
            ],
            [
                'key' => AccountingReport::GENERAL_LEDGER_REPORT,
                'report_bangla_name' => 'জেনারেল লেজার রিপোর্ট',
                'url' => url('/v2/accounting/reports/general_ledger_report'),
                'icon' => config('accounting_entry.icon_url').'/'.'general_ledger_report.png'
            ],
            [
                'key' => AccountingReport::CUSTOMER_WISE_SALES_REPORT,
                'report_bangla_name' => 'কাস্টমার আনুযায়ী বিক্রির রিপোর্ট',
                'url' => url('/v2/accounting/reports/pos/customer-wise'),
                'icon' => config('accounting_entry.icon_url').'/'.'customer_wise_sales_report.png'
            ],
            [
                'key' => AccountingReport::PRODUCT_WISE_SALES_REPORT,
                'report_bangla_name' => 'পণ্য অনুযায়ী বিক্রয় রিপোর্ট',
                'url' => url('/v2/accounting/reports/pos/product-wise'),
                'icon' => config('accounting_entry.icon_url').'/'.'item_wise_sales_report.png'
            ]
        ];
    }
}
