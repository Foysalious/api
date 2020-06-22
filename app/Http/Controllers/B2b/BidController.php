<?php namespace App\Http\Controllers\B2b;

use App\Models\Bid;
use App\Models\Procurement;
use App\Sheba\Business\Bid\Updater;
use App\Transformers\Business\BidDetailsTransformer;
use App\Transformers\Business\BidHistoryTransformer;
use App\Transformers\Business\ProcurementListTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Business\Bid\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use App\Sheba\Business\ACL\AccessControl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;

class BidController extends Controller
{
    use ModificationFields;

    /** @var BidRepositoryInterface */
    private $repo;

    public function __construct(BidRepositoryInterface $bid_repository)
    {
        $this->repo = $bid_repository;
    }

    public function index($business, $procurement, Request $request, AccessControl $access_control)
    {
        $access_control->setBusinessMember($request->business_member);
        if (!($access_control->hasAccess('procurement.r') || $access_control->hasAccess('procurement.rw'))) return api_response($request, null, 403);
        $business = $request->business;
        $procurement = Procurement::findOrFail((int)$procurement);
        $final_fields = collect();
        $procurement->load([
            'bids' => function ($q) {
                $q->where('status', '<>', 'pending')->with('items.fields');
            }
        ]);
        $bids = $procurement->bids;
        $bid_lists = [];
        $final_item = collect();
        foreach ($bids as $bid) {
            foreach ($bid->items as $item) {
                $final_item->push($item);
            }
        }
        $group_by_items = $final_item->groupBy('type');
        foreach ($group_by_items as $key => $group_by_item) {
            $fields = collect();
            foreach ($group_by_item as $item) {
                foreach ($item->fields as $field) {
                    $fields->push($field);
                }
            }
            $i = 0;
            foreach ($fields->groupBy('title') as $key => $titles) {
                foreach ($titles as $key => $title) {
                    $title['key'] = $i;
                    $final_fields->push($title);
                }
                $i++;
            }
        }
        foreach ($bids as $bid) {
            $model = $bid->bidder_type;
            $bidder = $model::findOrFail((int)$bid->bidder_id);
            $reviews = $bidder->reviews;

            $bid_items = $bid->items;
            $item_type = [];

            foreach ($bid_items as $item) {
                $item_fields = [];
                $fields = $item->fields;
                $total_price = 0;
                foreach ($fields as $field) {
                    $answer = null;
                    if ($item->type == 'price_quotation') {
                        $answer = $field->result;
                        $total_price += ($field->result);
                    } else {
                        $answer = $field->result;
                    }
                    array_push($item_fields, [
                        'field_id' => $field->id, 'question' => $field->title, 'answer' => $answer, 'input_type' => $field->input_type, 'key' => $final_fields->where('id', $field->id)->first()->key
                    ]);
                }
                array_push($item_type, [
                    'item_id' => $item->id, 'item_type' => $item->type, 'fields' => $item_fields, 'total_price' => $bid->price,
                ]);
            }

            array_push($bid_lists, [
                'id' => $bid->id, 'status' => $bid->status, 'bidder_name' => $bidder->name, 'bidder_logo' => $bidder->logo, 'is_favourite' => $bid->is_favourite, 'created_at' => $bid->created_at->format('d/m/y'), 'bidder_avg_rating' => round($reviews->avg('rating'), 2), 'item' => $item_type
            ]);
        }

        if (count($bid_lists) > 0) return api_response($request, $bid_lists, 200, ['bid_lists' => $bid_lists]); else return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $bid
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updateFavourite($business, $bid, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'is_favourite' => 'required|integer:in:1,0',
        ]);
        $bid = $this->repo->find((int)$bid);
        if (!$bid) {
            return api_response($request, null, 404);
        } else {
            $updater->setIsFavourite($request->is_favourite)->updateFavourite($bid);
            return api_response($request, null, 200);
        }
    }

    public function getBidHistory($business, $procurement, Request $request, AccessControl $access_control)
    {
        $access_control->setBusinessMember($request->business_member);
        if (!($access_control->hasAccess('procurement.r') || $access_control->hasAccess('procurement.rw'))) return api_response($request, null, 403);

        $procurement = Procurement::findOrFail((int)$procurement);

        list($offset, $limit) = calculatePagination($request);
        $bids = $procurement->bids()->orderBy('created_at', 'desc');

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($bids->get(), new BidHistoryTransformer($procurement));
        $bids = $manager->createData($resource)->toArray()['data'];

        if ($request->has('sort_by_name')) $bids = $this->sortByName($bids, $request->sort_by_name)->values();
        if ($request->has('sort_by_rating')) $bids = $this->sortByRating($bids, $request->sort_by_rating)->values();
        if ($request->has('sort_by_price')) $bids = $this->sortByPrice($bids, $request->sort_by_price)->values();
        if ($request->has('limit')) $bids = collect($bids)->splice($offset, $limit);

        if (count($bids) > 0) return api_response($request, $bids, 200, ['bids' => $bids]); else return api_response($request, null, 404);
    }

    /**
     * @param $bids
     * @param string $sort
     * @return mixed
     */
    private function sortByName($bids, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($bids)->$sort_by(function ($bid) {
            return strtoupper($bid['service_provider']['name']);
        });
    }

    /**
     * @param $bids
     * @param string $sort
     * @return mixed
     */
    private function sortByRating($bids, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($bids)->$sort_by(function ($bid) {
            return strtoupper($bid['service_provider']['rating']);
        });
    }

    /**
     * @param $bids
     * @param string $sort
     * @return mixed
     */
    private function sortByPrice($bids, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($bids)->$sort_by(function ($bid) {
            return strtoupper($bid['price']);
        });
    }

    /**
     * @param $business
     * @param $bid
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function sendHireRequest($business, $bid, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'terms' => 'required|string',
            'price' => 'required|numeric',
            'items' => 'required|string',
            'policies' => 'required|string'
        ]);
        $bid = $this->repo->find((int)$bid);
        $this->setModifier($request->manager_member);

        $updater->setBid($bid)
            ->setTerms($request->terms)
            ->setPolicies($request->policies)
            ->setItems(json_decode($request->items))
            ->setPrice($request->price)
            ->hire();

        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $bid
     * @param Request $request
     * @return JsonResponse
     */
    public function show($business, $bid, Request $request)
    {
        /** @var Bid $bid */
        $bid = $this->repo->find((int)$bid);
        $bid->load([
            'items' => function ($q) {
                $q->with([
                    'fields' => function ($q) {
                        $q->select('id', 'bid_item_id', 'title', 'short_description', 'input_type', 'variables', 'result');
                    }
                ]);
            }
        ]);

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($bid, new BidDetailsTransformer());
        $bid = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $bid, 200, ['bid' => $bid]);
    }

    /**
     * @param $business
     * @param $bid
     * @param Request $request
     * @return mixed
     */
    public function downloadPdf($business, $bid, Request $request)
    {
        /** @var Bid $bid */
        $bid = $this->repo->find((int)$bid);
        $bid->load([
            'items' => function ($q) {
                $q->with([
                    'fields' => function ($q) {
                        $q->select('id', 'bid_item_id', 'title', 'short_description', 'input_type', 'variables', 'result');
                    }
                ]);
            }
        ]);
        $price_quotation = $bid->items->where('type', 'price_quotation')->first();
        $technical_evaluation = $bid->items->where('type', 'technical_evaluation')->first();
        $company_evaluation = $bid->items->where('type', 'company_evaluation')->first();
        $bid_details = [
            'id' => $bid->id, 'procurement_id' => $bid->procurement_id, 'status' => $bid->status, 'price' => $bid->price, 'title' => $bid->procurement->title, 'type' => $bid->procurement->type, 'vendor' => [
                'name' => $bid->bidder->name, 'logo' => $bid->bidder->logo, 'domain' => $bid->bidder->sub_domain, 'rating' => round($bid->bidder->reviews->avg('rating'), 2), 'total_rating' => $bid->bidder->reviews->count()
            ], 'proposal' => $bid->proposal, 'start_date' => Carbon::parse($bid->procurement->procurement_start_date)->format('d/m/y'), 'end_date' => Carbon::parse($bid->procurement->procurement_end_date)->format('d/m/y'), 'created_at' => Carbon::parse($bid->created_at)->format('d/m/y'), 'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null, 'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null, 'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null,
        ];
        return App::make('dompdf.wrapper')->loadView('pdfs.quotation_details', compact('bid_details'))->download("quotation_details.pdf");
    }
}
