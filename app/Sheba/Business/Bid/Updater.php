<?php namespace App\Sheba\Business\Bid;


use App\Models\Bid;
use App\Sheba\Repositories\Business\BidRepository;
use Illuminate\Database\QueryException;
use Sheba\Repositories\Interfaces\BidItemFieldRepositoryInterface;
use DB;

class Updater
{
    private $bidRepository;
    private $bidItemFieldRepository;
    private $isFavourite;
    private $bidData;
    /** @var Bid */
    private $bid;
    private $status;
    private $terms;
    private $policies;
    private $items;
    private $price;

    public function __construct(BidRepository $bid_repository, BidItemFieldRepositoryInterface $bid_item_field_repository)
    {
        $this->bidRepository = $bid_repository;
        $this->bidItemFieldRepository = $bid_item_field_repository;
    }

    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    public function setPolicies($policies)
    {
        $this->policies = $policies;
        return $this;
    }

    public function setItems($item_fields)
    {
        $this->items = collect($item_fields);
        return $this;
    }

    public function setIsFavourite($is_favourite)
    {
        $this->isFavourite = $is_favourite;
        return $this;
    }

    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }


    public function updateFavourite(Bid $bid)
    {
        $this->bidData = [
            'is_favourite' => $this->isFavourite ? (int)$this->isFavourite : 0,
        ];
        $this->bidRepository->update($bid, $this->bidData);
    }

    public function hire()
    {
        try {
            DB::transaction(function () {
                $this->bidRepository->update($this->bid, ['status' => 'awarded', 'terms' => $this->terms, 'policies' => $this->policies]);
                if ($this->bid->isAdvanced()) {
                    $bid_price_quotation_item = $this->bid->items()->where('type', 'price_quotation')->first();
                    $price_quotation_item = $this->items->where('id', $bid_price_quotation_item->id)->first();
                    $fields = collect($price_quotation_item->fields);
                    foreach ($bid_price_quotation_item->fields as $field) {
                        $field_result = $fields->where('id', $field->id)->first();
                        if ($field_result) {
                            if ($field_result->unit) {
                                $variables = json_decode($field->variables);
                                $variables->unit = $field_result->unit;
                                $variables = json_encode($variables);
                            } else {
                                $variables = null;
                            }
                            $this->bidItemFieldRepository->update($field, [
                                'result' => isset($field_result->result) ? $field_result->result : $field->result,
                                'variables' => $variables ? $variables : $field->variables,
                                'title' => isset($field_result->title) ? $field_result->title : $field->title,
                                'short_description' => isset($field_result->short_description) ? $field_result->short_description : $field->short_description,
                            ]);
                        }
                    }
                }
                $this->updateBidPrice();
            });
        } catch (QueryException $e) {
            throw  $e;
        }
    }

    public function updateStatus()
    {
        $this->bidRepository->update($this->bid, ['status' => $this->status]);
    }

    public function updateBidPrice()
    {
        $bid_price_quotation_item = $this->bid->items()->where('type', 'price_quotation')->first();
        if ($bid_price_quotation_item) {
            $this->bidRepository->update($this->bid, ['price' => (double)$bid_price_quotation_item->fields->sum('result')]);
        } else {
            $this->bidRepository->update($this->bid, ['price' => (double)$this->price]);
        }
    }
}