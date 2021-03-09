<?php namespace App\Models;

use Sheba\Dal\BaseModel;
use Sheba\Dal\TopupOrder\Events\Created;
use Sheba\Dal\TopupOrder\Events\Updated;
use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Elasticsearch\ElasticsearchTrait;
use Sheba\Payment\PayableType;
use Sheba\TopUp\Gateway\Names;

class TopUpOrder extends BaseModel implements PayableType
{
    use ElasticsearchTrait;

    protected $guarded = ['id'];
    protected $table = 'topup_orders';
    protected $dates = ['created_at', 'updated_at'];

    public static $createdEventClass = Created::class;
    public static $updatedEventClass = Updated::class;

    /**
     * The elasticsearch settings.
     *
     * @var array
     */
    protected $indexSettings = [
        'analysis' => [
            'char_filter' => [
                'replace' => [
                    'type' => 'mapping', 'mappings' => [
                        '&=> and '
                    ],
                ],
            ],
            'filter' => [
                'word_delimiter' => [
                    'type' => 'word_delimiter', 'split_on_numerics' => false, 'split_on_case_change' => true, 'generate_word_parts' => true, 'generate_number_parts' => true, 'catenate_all' => true, 'preserve_original' => true, 'catenate_numbers' => true,
                ]
            ],
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'char_filter' => [
                        'html_strip', 'replace'
                    ],
                    'tokenizer' => 'whitespace',
                    'filter' => [
                        'lowercase', 'word_delimiter'
                    ]
                ],
                'custom_like_query_analyzer' => [
                    'tokenizer' => 'custom_like_query_tokenizer'
                ]
            ],
            'tokenizer' => [
                'custom_like_query_tokenizer' => [
                    "type" => "ngram",
                    "min_gram" => 2,
                    "max_gram" => 14,
                    "token_chars" => ["letter", "digit"]
                ]
            ]
        ],
        'max_ngram_diff' => 12
    ];

    protected $mappingProperties = [
        'id' => ['type' => 'integer'],
        'payee_mobile_type' => ['type' => 'keyword'],
        'gateway' => ['type' => 'keyword'],
        'sheba_commission' => ['type' => 'double'],
        'agent_commission' => ['type' => 'double'],
        'ambassador_commission' => ['type' => 'double'],
        'vendor_id' => ['type' => 'integer'],
        'status' => ['type' => 'keyword'],
        'transaction_id' => ['type' => 'text'],
        'agent_type' => ['type' => 'keyword'],
        'agent_id' => ['type' => 'integer'],
        'amount' => ['type' => 'double'],
        'payee_mobile' => ['type' => 'text', "analyzer" => "custom_like_query_analyzer", "search_analyzer" => "standard"],
        'created_at' => ['type' => 'date', "format" => "yyyy-MM-dd HH:mm:ss"],
        'updated_at' => ['type' => 'date', "format" => "yyyy-MM-dd HH:mm:ss"]
    ];

    public function getIndexName(): string
    {
        return $this->getTable();
    }

    public function agent()
    {
        return $this->morphTo();
    }

    public function vendor()
    {
        return $this->belongsTo(TopUpVendor::class);
    }

    public function isAgentPartner()
    {
        return $this->agent_type == Partner::class;
    }

    public function isAgentAffiliate()
    {
        return $this->agent_type == Affiliate::class;
    }

    public function getAgentNameAttribute()
    {
        if ($this->isAgentPartner()) return $this->agent->name;
        elseif ($this->isAgentAffiliate()) return $this->agent->profile->name;
    }

    public function getAgentMobileAttribute()
    {
        if ($this->isAgentPartner()) return $this->agent->contact_no;
        elseif ($this->isAgentAffiliate()) return $this->agent->profile->mobile;
    }

    public function isFailed()
    {
        return $this->status == Statuses::FAILED;
    }

    public function isFailedDueToGatewayTimeout()
    {
        return $this->isFailed() && $this->failed_reason == FailedReason::GATEWAY_TIMEOUT;
    }

    public function isSuccess()
    {
        return $this->status == Statuses::SUCCESSFUL;
    }

    public function isPending()
    {
        return $this->status == Statuses::PENDING;
    }

    public function isProcessed()
    {
        return $this->isFailed() || $this->isSuccess();
    }

    public function scopeProcessed($query)
    {
        return $query->statuses(Statuses::getProcessed());
    }

    public function scopeStatus($query, $status)
    {
        return $query->whereIn('status', $status);
    }

    public function scopeStatuses($query, $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeOperator($query, $vendor_id)
    {
        return $query->where('vendor_id', $vendor_id);
    }

    public function scopeGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function getOriginalMobile()
    {
        return getOriginalMobileNumber($this->payee_mobile);
    }

    public function isRobiWalletTopUp()
    {
        return !!$this->is_robi_topup_wallet;
    }

    public function isViaPaywell()
    {
        return $this->gateway == Names::PAYWELL;
    }

    public function getGatewayRefId()
    {
        return dechex($this->id);
    }
}
