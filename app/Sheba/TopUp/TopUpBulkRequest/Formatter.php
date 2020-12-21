<?php namespace App\Sheba\TopUp\TopUpBulkRequest;

use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;

class Formatter
{
    /** @var TopUpBulkRequest $topUpBulkRequest */
    private $topUpBulkRequest;
    private $agent;
    private $agentType;

    /**
     * Formatter constructor.
     * @param TopUpBulkRequest $topup_bulk_request
     */
    public function __construct(TopUpBulkRequest $topup_bulk_request)
    {
        $this->topUpBulkRequest = $topup_bulk_request;
    }

    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function setAgentType($agent_type)
    {
        $this->agentType = $agent_type;
        return $this;
    }

    public function format()
    {
        $topup_bulk_requests = $this->topUpBulkRequest->select('id', 'created_at')->where([['agent_id', $this->agent->id], ['agent_type', $this->agentType]])->orderBy('id', 'desc')->get();
        $data = [];
        foreach ($topup_bulk_requests as $topup_bulk_request) {
            array_push($data, [
                'id' => $topup_bulk_request->id,
                'value' => 'Bulk ID: ' . $topup_bulk_request->id . ' ' . $topup_bulk_request->created_at->format('jS M, Y h:i A')
            ]);
        }

        return $data;
    }
}
