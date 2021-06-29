<?php namespace App\Models;

use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceCreated;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sheba\Dal\BaseModel;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceUpdated;
use Sheba\Dal\PartnerPosServiceBatch\Model as PartnerPosServiceBatch;
use Sheba\Dal\PartnerPosServiceImageGallery\Model as PartnerPosServiceImageGallery;
use Sheba\Elasticsearch\ElasticsearchTrait;


class PartnerPosService extends BaseModel
{
    use SoftDeletes, ElasticsearchTrait;

    protected $guarded = ['id'];
    protected $casts = ['cost' => 'double', 'price' => 'double', 'stock' => 'double', 'vat_percentage' => 'double', 'show_image' => 'int'];
    protected $dates = ['deleted_at'];

    public static $savedEventClass = PartnerPosServiceSaved::class;

//    public function setNameAttribute($name)
//    {
//        $this->attributes['name']=json_encode($name);
//    }
//    public function getNameAttribute($name)
//    {
//        return json_decode($name);
//    }

//    public function setDescriptionAttribute($description)
//    {
//        $this->attributes['description']=json_encode($description);
//    }

//    public function getDescriptionAttribute($description){
//
//        return json_decode($description);
//    }
    public static $autoIndex = false;
    protected $indexSettings = [
        'analysis' => [
            "analyzer" => [
                "pos_service_search_analyzer" => [
                    "type" => "standard",
                    "filter" => ["lowercase", "asciifolding"],
                    "max_token_length" => 2,
                ]
            ]
        ],
    ];
    protected $mappingProperties = [
        'id' => ['type' => 'integer'],
        'partner_id' => ['type' => 'integer'],
        'pos_category_id' => ['type' => 'integer'],
        'name' => ['type' => 'text', 'analyzer' => 'pos_service_search_analyzer'],
        'description' => ['type' => 'text', 'analyzer' => 'pos_service_search_analyzer'],
        'stock' => ['type' => 'double'],
        'is_published_for_shop' => ['type' => 'integer'],
        'created_at' => ['type' => 'date', "format" => "yyyy-MM-dd HH:mm:ss"],
        'updated_at' => ['type' => 'date', "format" => "yyyy-MM-dd HH:mm:ss"]
    ];

    public function getIndexDocumentData(): array
    {
        return [
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'pos_category_id' => $this->pos_category_id,
            'name' => $this->name,
            'description' => $this->description,
            'stock' => (double)$this->getStock(),
            'is_published_for_shop' => +$this->is_published_for_shop,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

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
        return round((($discount->amount / $this->price) * 100), 1);
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
            'stock' => (double)$this->getStock(),
            'description' => $this->description,
            'publication_status' => (int)$this->publication_status,
            'is_published_for_shop' => (int)$this->is_published_for_shop,
            'app_thumb' => $this->app_thumb,
        ];
    }

    public function scopeWebstorePublishedServiceByPartner($query, $partner_id)
    {
        return $query->where([['partner_id', $partner_id], ['is_published_for_shop', 1]]);
    }

    /**
     * @return bool
     */
    public function isWebstorePublished(): bool
    {
        return $this->is_published_for_shop == 1;
    }

    public function stock()
    {
        return $this->hasMany(PartnerPosServiceBatch::class);
    }

    public function getStock()
    {
        return $this->stock()->get()->sum('stock');
    }

    public function cost()
    {
        return $this->hasMany(PartnerPosServiceBatch::class);
    }

    public function getLastCost()
    {
        return $this->cost()->latest()->first()->cost ? $this->cost()->latest()->first()->cost : 0.0;
    }
}
