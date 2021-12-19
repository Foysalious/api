<?php namespace App\Http\Controllers;

use App\Exceptions\HyperLocationNotFoundException;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Partner;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\Checkout\Validation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\Partners\LitePartnerList;
use Sheba\Checkout\PartnerSort;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Dal\PartnerLocation\PartnerLocation;
use Sheba\Dal\PartnerLocation\PartnerLocationRepository;
use Throwable;

class PartnerLocationController extends Controller
{
    const COMPLIMENT_QUESTION_ID = 2;

    /** @var ReviewRepository $reviewRepository */
    private $reviewRepository;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
    }


    public function getPartners(Request $request, PartnerListRequest $partnerListRequest)
    {
        $this->validate($request, [
            'date' => 'sometimes|required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'), 'time' => 'sometimes|required|string', 'services' => 'required|string', 'isAvailable' => 'sometimes|required', 'partner' => 'sometimes|required', 'lat' => 'required|numeric', 'lng' => 'required|numeric', 'skip_availability' => 'numeric', 'filter' => 'string|in:sheba',
        ]);
        $validation = new Validation($request);
        if (!$validation->isValid()) return api_response($request, $validation->message, 400, ['message' => $validation->message]);
        $partner = $request->filled('partner') ? $request->partner : null;
        $partnerListRequest->setRequest($request)->prepareObject();
        $partner_list = new PartnerList();
        $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
        if ($request->filled('isAvailable')) {
            $partners = $partner_list->partners;
            $available_partners = $partners->filter(function ($partner) {
                return $partner->is_available == 1;
            });
            $is_available = count($available_partners) != 0 ? 1 : 0;
            return api_response($request, $is_available, 200, ['is_available' => $is_available, 'available_partners' => count($available_partners)]);
        }
        if ($request->filled('show_reason')) return api_response($request, null, 200, ['reason' => $partner_list->getNotShowingReason()]);
        if ($partner_list->hasPartners) {
            $partner_list->addPricing();
            $partner_list->addInfo();
            if ($request->filled('filter') && $request->filter == 'sheba') {
                $partner_list->sortByShebaPartnerPriority();
            } else {
                $partner_list->sortByShebaSelectedCriteria();
            }
            $partners = $partner_list->removeKeysFromPartner()->values()->all();
            return api_response($request, $partners, 200, ['partners' => $partners]);
        }
        return api_response($request, null, 404, ['message' => 'No partner found.']);
    }

    public function getLitePartners(Request $request, PartnerListRequest $partnerListRequest)
    {
        try {
            $this->validate($request, [
                'services' => 'required|string', 'partner' => 'sometimes|required', 'lat' => 'required|numeric', 'lng' => 'required|numeric', 'partner_count' => 'numeric',
            ]);
            return api_response($request, null, 404, ['message' => 'No partner found.']);
            $partner = $request->filled('partner') ? $request->partner : null;
            $partner_count = $request->filled('partner_count') ? (int)$request->partner_count : 0;
            if ($partner_count < 50 && $request->services != 'null') {
                $partnerListRequest->setRequest($request)->prepareObject();
                $partner_list = new PartnerList();
                $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
                $lite_list = new LitePartnerList();
                $lite_list->setPartnerListRequest($partnerListRequest)->setLimit(50 - $partner_count)->find($partner);
                $lite_list->addInfo();
                $lite_partners = $lite_list->removeKeysFromPartner()->values()->all();
                if (count($lite_partners) > 0) return api_response($request, null, 200, ['partners' => $lite_partners]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        } catch (HyperLocationNotFoundException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 400, ['message' => 'Your are out of service area.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param PartnerLocationRepository $partnerLocationRepository
     * @return JsonResponse
     */
    public function getNearbyPartners(Request $request, PartnerLocationRepository $partnerLocationRepository)
    {
        try {
            ini_set('memory_limit', '512M');
            $this->validate($request, ['lat' => 'required', 'lng' => 'required']);
            $location = null;

            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location;

            if (!$location) return api_response($request, 'Invalid location', 400, ['message' => 'Invalid location']);

            $nearByPartners = $partnerLocationRepository->findNearByPartners((double)$request->lat, (double)$request->lng)->pluckMultiple(['distance', 'location'], 'partner_id', true);

            $partners = Partner::where(function ($query) {
                $query->where(function ($query) {
                    $query->verified();
                })->orWhere(function ($query) {
                    $query->where(function ($lite_sp_filtering_query) {
                        $lite_sp_filtering_query->lite();
                    })->where('moderation_status', 'approved');
                });
            })->with([
                'subscription', 'categories' => function ($category_query) {
                    $category_query->with([
                        'parent' => function ($master_category_query) {
                            $master_category_query->select('id', 'name');
                        }
                    ])->select('parent_id');
                }
            ]);

            if ($request->filled('q')) $partners = $partners->where('name', 'like', '%' . $request->q . '%');

            $partners = $partners->whereIn('id', $nearByPartners->keys())->get();

            if ($request->filled('category_id')) $partners = $partners->filter(function ($partner) use ($request) {
                return in_array($request->category_id, $partner->servingMasterCategoryIds());
            });

            $reviews = Review::select('partner_id', DB::raw('avg(rating) as avg_rating'))->groupBy('partner_id')->whereIn('partner_id', $nearByPartners->keys())->get()->pluck('avg_rating', 'partner_id');

            $partnersWithLiteSps = $partners;
            $partners = (new PartnerSort($partners))->get()->take(50);
            $partnerDetails = collect();
            $this->formatCollection($partners, $nearByPartners, $request, $reviews, $partnerDetails);
            $partnerDetails = $partnerDetails->sortBy('distance')->values();
            $liteSps = $partnersWithLiteSps->filter(function ($partner) {
                return $partner->isLite();
            })->take(20);

            $this->formatCollection($liteSps, $nearByPartners, $request, $reviews, $partnerDetails);

            return api_response($request, null, 200, ['partners' => $partnerDetails]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param $partners
     * @param $nearByPartners
     * @param $request
     * @param $reviews
     * @param $partnerDetails
     */
    private function formatCollection($partners, $nearByPartners, $request, $reviews, &$partnerDetails)
    {
        foreach ($partners as $partner) {
            $serving_master_categories = $partner->servingMasterCategories();
            $partner->lat = $nearByPartners[$partner->id]->location->coordinates[1];
            $partner->lng = $nearByPartners[$partner->id]->location->coordinates[0];
            $partner->distance = round($nearByPartners[$partner->id]->distance, 2);
            $partner->badge = $partner->resolveBadge();
            $partner->rating = $reviews->has($partner->id) ? round($reviews[$partner->id], 2) : 0.00;
            $partner->serving_category = $serving_master_categories;
            removeRelationsAndFields($partner);
            $partnerDetails->push($partner);
        }
    }
}
