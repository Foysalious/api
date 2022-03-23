<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbConstants;
use App\Sheba\MTB\MtbServerClient;

class MtbSaveTransaction
{
    /**
     * @var Partner
     */
    private $partner;

    public function __construct(MtbServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    private function makeData()
    {
        return [
            'RequestData' => [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'StockAmt' => "0",
                'MonthlyTotalSale' => "0",
                'MonthlyIncome' => "0",
                'MonthlyExpense' => "0",
                'LoanOutstanding' => "0",
                'TotalIncome' => "0",
                'MonthlyMercSale' => "0"
            ],
            'requestId' => strval($this->partner->id),
            'channelId' => MtbConstants::CHANNEL_ID,
        ];
    }

    public function saveTransactionInformation()
    {
        $data = $this->makeData();
        return $this->client->post('api/acctOpen/saveTransactionInformation', $data, AuthTypes::BARER_TOKEN);

    }
}
