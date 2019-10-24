<?php namespace Sheba\Business\Bid;

use App\Models\Bid;
use App\Models\Procurement;
use App\Sheba\Repositories\Business\BidRepository;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Repositories\Interfaces\BidItemFieldRepositoryInterface;
use Sheba\Repositories\Interfaces\BidItemRepositoryInterface;

class Creator
{
    private $bidRepository;
    private $procurement;
    private $data;
    private $bidder;
    private $status;
    private $terms;
    private $policies;
    private $price;
    private $proposal;
    private $fieldResults;
    private $bidItemRepository;
    private $bidItemFieldRepository;


    public function __construct(BidRepository $bid_repository, BidItemRepositoryInterface $bid_item_repository, BidItemFieldRepositoryInterface $bid_item_field_repository)
    {
        $this->bidRepository = $bid_repository;
        $this->bidItemRepository = $bid_item_repository;
        $this->bidItemFieldRepository = $bid_item_field_repository;
        $this->data = [];
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setBidder(Bidder $bidder)
    {
        $this->bidder = $bidder;
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

    public function setProposal($proposal)
    {
        $this->proposal = $proposal;
        return $this;
    }

    public function setFieldResults($field_results)
    {
        $this->fieldResults = collect($field_results);
        return $this;
    }

    public function setPrice($price)
    {
        $this->price = (double)$price;
        return $this;
    }


    public function create()
    {
        $this->makeData();
        $bid = null;
        try {
            DB::transaction(function () use (&$bid) {
                /** @var Bid $bid */
                $bid = $this->bidRepository->create($this->data);
                foreach ($this->procurement->items as $item) {
                    $bid_item = $this->bidItemRepository->create(['bid_id' => $bid->id, 'type' => $item->type, 'proposal' => $this->proposal]);
                    foreach ($item->fields as $field) {
                        $field_result = $this->fieldResults->where('id', $field->id)->first();
                        $this->bidItemFieldRepository->create([
                            'bid_item_id' => $bid_item->id,
                            'title' => $field->title,
                            'short_description' => $field->short_description,
                            'input_type' => $field->input_type,
                            'variables' => $field->variables,
                            'result' => $field_result ? $field_result->result : null
                        ]);
                    }
                }
                $this->updatePrice($bid);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return $bid;
    }

    private function makeData()
    {
        $this->data['procurement_id'] = $this->procurement->id;
        $this->data['bidder_id'] = $this->bidder->id;
        $this->data['bidder_type'] = get_class($this->bidder);
        $this->data['status'] = $this->status;
    }

    private function updatePrice(Bid $bid)
    {
        if ($this->price) {
            $this->bidRepository->update($bid, ['price' => $this->price]);
        } else {
            $price_item = $this->bidItemRepository->where('type', 'price_quotation')->first();
            if ($price_item) {
                $price = 0;
                foreach ($price_item->fields as $field) {
                    $variables = json_decode($field->variables);
                    $price += ((double)$variables->unit * (double)$field->result);
                }
                $this->bidRepository->update($bid, ['price' => $price]);
            }
        }
    }
}