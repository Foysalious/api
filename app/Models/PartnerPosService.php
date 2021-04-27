<?php namespace App\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sheba\Dal\BaseModel;
use Sheba\Dal\PartnerPosServiceImageGallery\Model as PartnerPosServiceImageGallery;
use Sheba\Elasticsearch\ElasticsearchTrait;


class PartnerPosService extends BaseModel
{
    use SoftDeletes, ElasticsearchTrait;

    protected $guarded = ['id'];
    protected $casts = ['cost' => 'double', 'price' => 'double', 'stock' => 'double', 'vat_percentage' => 'double', 'show_image' => 'int'];
    protected $dates = ['deleted_at'];

//    public static $savedEventClass = PartnerPosServiceSaved::class;
    public static $autoIndex = false;
    protected $indexSettings = [
        'analysis' => [
            'char_filter' => [
                'replace' => [
                    'type' => 'mapping',
                    'mappings' => [
                        '&=> and '
                    ],
                ],
            ],
            'filter' => [
                'word_delimiter' => [
                    'type' => 'word_delimiter',
                    'split_on_numerics' => false,
                    'split_on_case_change' => true,
                    'generate_word_parts' => true,
                    'generate_number_parts' => true,
                    'catenate_all' => true,
                    'preserve_original' => true,
                    'catenate_numbers' => true,
                ]
            ],
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'char_filter' => [
                        'html_strip',
                        'replace',
                    ],
                    'tokenizer' => 'whitespace',
                    'filter' => [
                        'lowercase',
                        'word_delimiter',
                    ],
                ],
            ],
        ],
    ];
    protected $mappingProperties = [
        'id' => ['type' => 'integer'],
        'partner_id' => ['type' => 'integer'],
        'pos_category_id' => ['type' => 'integer'],
        'name' => ['type' => 'text', 'analyzer' => 'standard'],
        'description' => ['type' => 'text', 'analyzer' => 'standard'],
        'publication_status' => ['type' => 'integer'],
        'is_published_for_shop' => ['type' => 'integer'],
        'created_at' => ['type' => 'date', "format" => "yyyy-MM-dd HH:mm:ss"],
        'updated_at' => ['type' => 'date', "format" => "yyyy-MM-dd HH:mm:ss"]
    ];

    public function getIndexName(): string
    {
        return $this->getTable();
    }

    public $algoliaSettings = [
        'searchableAttributes' => [
            'name',
            'description',
        ],
        'attributesForFaceting' => ['partner'],
        'unretrievableAttributes' => [
            'partner'
        ]
    ];

    public function subCategory()
    {
        return $this->category()->with('parent');
    }

    public function category()
    {
        return $this->belongsTo(PosCategory::class, 'pos_category_id');
    }

    public function imageGallery()
    {
        return $this->hasMany(PartnerPosServiceImageGallery::class);
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 1);
    }

    public function scopePublishedForShop($query)
    {
        return $query->where('is_published_for_shop', 1);
    }

    /**
     * Scope a query to only include a specific master category.
     *
     * @param Builder $query
     * @param $master_category_id
     * @return Builder
     */
    public function scopeOfParentCategory($query, $master_category_id)
    {
        return $query->whereHas('category', function ($q) use ($master_category_id) {
            $q->where('parent_id', $master_category_id);
        });
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopePartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    public function discounts()
    {
        return $this->hasMany(PartnerPosServiceDiscount::class);
    }

    public function getPriceAttribute($price)
    {
        return $price ?: 0.0;
    }

    public function getDiscountedAmount()
    {
        $amount = $this->price - $this->getDiscount();
        return ($amount < 0) ? 0 : (float)$amount;
    }

    public function getDiscount()
    {
        $discount = $this->discount();
        if ($discount->is_amount_percentage) {
            $amount = ($this->price * $discount->amount) / 100;
            if ($discount->hasCap()) {
                $amount = ($amount > $discount->cap) ? $discount->cap : $amount;
            }
        } else {
            $amount = $discount->amount;
        }

        return ($amount < 0) ? 0 : (float)$amount;
    }

    public function discount()
    {
        return $this->runningDiscounts()->first();
    }

    public function getDiscountPercentage()
    {
        if ($this->price == 0)
            return 0;
        $discount = $this->discount();
        if ($discount->is_amount_percentage)
            return $discount->amount;
        return round((($discount->amount/ $this->price) * 100),1);
    }

    public function runningDiscounts()
    {
        $now = Carbon::now();
        /**
         * USING AS A QUERY, THAT INCREASING LOAD TIME ON LIST VIEW
         *
         * return $this->discounts()->where(function ($query) use ($now) {
         * $query->where('start_date', '<=', $now);
         * $query->where('end_date', '>=', $now);
         * })->get();*/
        return $this->discounts->filter(function ($discount) use ($now) {
            return $discount->start_date <= $now && $discount->end_date >= $now;
        });
    }

    public function logs()
    {
        return $this->hasMany(PartnerPosServiceLog::class);
    }

    public function getAlgoliaRecord()
    {
        return [
            'id' => (int)$this->id,
            'partner_id' => (int)$this->partner_id,
            'category_id' => (int)$this->pos_category_id,
            'category_name' => $this->category->name,
            'name' => $this->name,
            'stock' => (double)$this->stock,
            'description' => $this->description,
            'publication_status' => (int)$this->publication_status,
            'is_published_for_shop' => (int)$this->is_published_for_shop,
            'app_thumb' => $this->app_thumb,
        ];
    }

    public function scopeWebstorePublishedServiceByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id)->where('publication_status', 1)->where('is_published_for_shop', 1);
    }

    /**
     * @return bool
     */
    public function isWebstorePublished(): bool
    {
        return $this->is_published_for_shop == 1;
    }
}
