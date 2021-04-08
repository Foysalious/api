<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Business;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Sheba\Dal\Category\Category;
use App\Models\Partner;
use App\Models\Procurement;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\Tag;
use App\Sheba\Bitly\BitlyLinkShort;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\Bid\Updater as BidUpdater;
use App\Sheba\Business\Procurement\ProcurementOrder;
use App\Sheba\Business\Procurement\Updater;
use App\Transformers\Business\ProcurementDetailsTransformer;
use App\Transformers\Business\ProcurementInvitationListTransformer;
use App\Transformers\Business\ProcurementListTransformer;
use App\Transformers\Business\ProposalDetailsTransformer;
use App\Transformers\Business\TenderDetailsTransformer;
use App\Transformers\Business\TenderMinimalTransformer;
use App\Transformers\Business\TenderTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Business\Bid\Creator as BidCreator;
use Sheba\Business\Bid\Statuses as BidStatuses;
use Sheba\Business\Procurement\Creator;
use Sheba\Business\Procurement\ProcurementFilterRequest;
use Sheba\Business\Procurement\RequestHandler;
use Sheba\Business\Procurement\StatusCalculator;
use Sheba\Business\Procurement\StatusCalculator as ProcurementStatusCalculator;
use Sheba\Business\Procurement\Statuses;
use Sheba\Business\Procurement\WorkOrderDataGenerator;
use Sheba\Dal\Procurement\PublicationStatuses;
use Sheba\Dal\ProcurementInvitation\ProcurementInvitationRepositoryInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Partner\CreateRequest as PartnerCreateRequest;
use Sheba\Partner\Creator as PartnerCreator;
use Sheba\Partner\PartnerStatuses;
use Sheba\Payment\Adapters\Payable\ProcurementAdapter;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\ShebaPaymentValidator;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\Repositories\ProfileRepository;
use Sheba\Resource\Creator\ResourceCreateRequest;
use Sheba\Resource\ResourceCreator;
use Sheba\Sms\Sms;
use Sheba\Business\ProcurementInvitation\Creator as ProcurementInvitationCreator;
use Sheba\Business\Procurement\BasicInfoUpdater as BasicInfoUpdater;
use Sheba\Business\Procurement\AttachmentUpdater;
use Sheba\Business\Procurement\DescriptionUpdater;
use Sheba\Dal\Bid\Contract as BidRepository;

class ProcurementController extends Controller
{
    use ModificationFields;

    /** @var ProcurementRepositoryInterface $procurementRepository */
    private $procurementRepository;
    /** @var Resource $resource */
    private $resource;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var ResourceCreator $resourceCreator */
    private $resourceCreator;
    /** @var PartnerCreator $partnerCreator */
    private $partnerCreator;
    /** @var PartnerCreateRequest $partnerCreateRequest */
    private $partnerCreateRequest;
    /** @var ProcurementFilterRequest $procurementFilterRequest */
    private $procurementFilterRequest;
    /** @var ProcurementOrder $procurementOrder */
    private $procurementOrder;
    /** @var RequestHandler $procurementRequestHandler */
    private $procurementRequestHandler;
    /** @var ResourceCreateRequest $resourceCreateRequest */
    private $resourceCreateRequest;

    private $bid_repository;

    /**
     * ProcurementController constructor.
     *
     * @param ProcurementRepositoryInterface $procurement_repository
     * @param ProfileRepository $profile_repo
     * @param ResourceCreator $resource_creator
     * @param PartnerCreator $partner_creator
     * @param PartnerCreateRequest $partner_create_request
     * @param ProcurementFilterRequest $procurement_filter_request
     * @param ProcurementOrder $procurement_order
     * @param RequestHandler $procurement_request_handler
     * @param ResourceCreateRequest $resource_create_request
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repository,
                                ProfileRepository $profile_repo,
                                ResourceCreator $resource_creator,
                                PartnerCreator $partner_creator,
                                PartnerCreateRequest $partner_create_request,
                                ProcurementFilterRequest $procurement_filter_request,
                                ProcurementOrder $procurement_order,
                                RequestHandler $procurement_request_handler,
                                ResourceCreateRequest $resource_create_request)
    {
        $this->procurementRepository = $procurement_repository;
        $this->profileRepository = $profile_repo;
        $this->resourceCreator = $resource_creator;
        $this->partnerCreator = $partner_creator;
        $this->partnerCreateRequest = $partner_create_request;
        $this->procurementFilterRequest = $procurement_filter_request;
        $this->procurementOrder = $procurement_order;
        $this->procurementRequestHandler = $procurement_request_handler;
        $this->resourceCreateRequest = $resource_create_request;
    }

    public function create(Request $request)
    {
        $uncategorised = ['id' => 0, 'name' => 'uncategorised'];
        $categories = Category::child()->published()->publishedForB2B()->select('id', 'name')->get()->toArray();
        $categories = array_merge([$uncategorised], $categories);
        $sharing_to = config('b2b.SHARING_TO');
        $payment_strategy = config('b2b.PAYMENT_STRATEGY');
        $number_of_participants = config('b2b.NUMBER_OF_PARTICIPANTS');
        $procurements = [
            'sharing_to' => array_values($sharing_to),
            'payment_strategy' => $payment_strategy,
            'number_of_participants' => $number_of_participants,
            'categories' => $categories
        ];
        return api_response($request, $procurements, 200, ['procurements' => $procurements]);
    }

    public function getTags(Request $request)
    {
        $tags = Tag::where('taggable_type', 'App\Models\Procurement')->select('id', 'name')->get();

        if ($request->has('search')) {
            $tags = $tags->filter(function ($tag) use ($request) {
                return str_contains(strtoupper($tag->name), strtoupper($request->search));
            });
        }
        return api_response($request, $tags, 200, ['tags' => $tags->values()]);
    }

    /**
     * @param Request $request
     * @param AccessControl $access_control
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, AccessControl $access_control, Creator $creator)
    {
        $procurement_shared_to_options = implode(',', array_column(config('b2b.SHARING_TO'), 'key'));
        $this->validate($request, [
            'description' => 'required|string',
            'procurement_start_date' => 'required|date_format:Y-m-d',
            'procurement_end_date' => 'required|date_format:Y-m-d',
            'last_date_of_submission' => 'required|date_format:Y-m-d',
            'number_of_participants' => 'required|numeric',
            'sharing_to' => 'required|required|in:' . $procurement_shared_to_options,
            'type' => 'sometimes|required|string:in:basic,advanced',
            'title' => 'sometimes|required|string',
            'payment_options' => 'sometimes|required|string',
            'items' => 'sometimes|required|string',
            'is_published' => 'sometimes|required|integer',
            'attachments.*' => 'file'
        ]);

        if (!$access_control->setBusinessMember($request->business_member)->hasAccess('procurement.rw'))
            return api_response($request, null, 403, ['message' => 'You don\'t have permission to create this Tender']);

        $this->setModifier($request->manager_member);

        $creator->setLongDescription($request->description)
            ->setProcurementStartDate($request->procurement_start_date)
            ->setProcurementEndDate($request->procurement_end_date)
            ->setLastDateOfSubmission($request->last_date_of_submission)
            ->setNumberOfParticipants($request->number_of_participants)
            ->setSharingTo($request->sharing_to)
            ->setLabels($request->labels)
            ->setTitle($request->title)
            ->setCategory($request->category_id)
            ->estimatedPrice($request->estimated_price)
            ->setItems($request->items)
            ->setQuestions($request->questions)
            ->setPaymentOptions($request->payment_options)
            ->setIsPublished($request->is_published)
            ->setOwner($request->business)
            ->setCreatedBy($request->manager_member)
            ->setType($request->type)
            ->setPurchaseRequest($request->purchase_request_id)
            ->setOrderStartDate($request->order_start_date)
            ->setOrderEndDate($request->order_end_date)
            ->setInterviewDate($request->interview_date);

        if ($request->attachments && is_array($request->attachments))
            $creator->setAttachments($request->attachments);

        $procurement = $creator->create();

        $share = $this->getShareInformation($procurement);
        return api_response($request, $procurement, 200, ['id' => $procurement->id, 'share' => $share]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param AccessControl $access_control
     * @param BidRepository $bid_repository
     * @return JsonResponse
     */
    public function index($business, Request $request, AccessControl $access_control, BidRepository $bid_repository)
    {
        $this->validate($request, [
            'status' => 'sometimes|string',
        ]);
        $access_control->setBusinessMember($request->business_member);
        if (!($access_control->hasAccess('procurement.r') || $access_control->hasAccess('procurement.rw'))) return api_response($request, null, 403);
        $this->setModifier($request->manager_member);
        $business = $request->business;

        list($offset, $limit) = calculatePagination($request);
        $procurements = $this->procurementRepository->ofBusiness($business->id)
            ->select(['id', 'title', 'long_description', 'status', 'last_date_of_submission', 'created_at', 'is_published', 'publication_status'])
            ->orderBy('id', 'desc');

        $is_procurement_available = $procurements->count() > 0 ? 1 : 0;

        #if ($request->has('status') && $request->status != 'all') $procurements = $this->procurementRepository->filterWithStatus($request->status);
        $start_date = $request->has('start_date') ? $request->start_date : null;
        $end_date = $request->has('end_date') ? $request->end_date : null;
        if ($start_date && $end_date) $procurements = $this->procurementRepository->filterWithCreatedAt($start_date, $end_date);

        $bid_counts = $bid_repository->getActiveProcurementBidderCount();

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurements->get(), new ProcurementListTransformer($bid_counts));
        $procurements = $manager->createData($resource)->toArray()['data'];

        if ($request->has('status') && $request->status != 'all') $procurements = $this->filterWithStatus($procurements, $request->status);
        if ($request->has('search')) $procurements = $this->searchByTitle($procurements, $request)->values();
        if ($request->has('sort_by_id')) $procurements = $this->sortById($procurements, $request->sort_by_id)->values();
        if ($request->has('sort_by_title')) $procurements = $this->sortByTitle($procurements, $request->sort_by_title)->values();
        if ($request->has('sort_by_created_at')) $procurements = $this->sortByCreatedAt($procurements, $request->sort_by_created_at)->values();
        $total_procurement = count($procurements);
        if ($request->has('limit')) $procurements = collect($procurements)->splice($offset, $limit);

        if (count($procurements) > 0) return api_response($request, $procurements, 200, [
            'procurements' => $procurements, 'total_procurement' => $total_procurement, 'is_procurement_available' => $is_procurement_available
        ]);
        else return api_response($request, null, 404);
    }

    /**
     * @param $procurements
     * @param $status
     * @return \Illuminate\Support\Collection
     */
    public function filterWithStatus($procurements, $status)
    {
        if ($status === 'draft') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'unpublished') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'open') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'hired') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'expired') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
        if ($status === 'closed') return collect($procurements)->filter(function ($procurement) use ($status) {
            return strtoupper($procurement['status']) == strtoupper($status);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortById($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['id']);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortByTitle($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['title']);
        });
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return mixed
     */
    private function sortByCreatedAt($procurements, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement) {
            return strtoupper($procurement['created_at']);
        });
    }

    /**
     * @param $procurements
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    private function searchByTitle($procurements, Request $request)
    {
        return collect($procurements)->filter(function ($procurement) use ($request) {
            return str_contains(strtoupper($procurement['title']), strtoupper($request->search));
        });
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filterOptions(Request $request)
    {
        $categories = Category::child()->published()->publishedForB2B()->select('id', 'name')->get()->toArray();
        $tags = Tag::with('taggables')->where('taggable_type', 'App\Models\Procurement')->select('id', 'name', 'taggable_type')->get();
        $tag_lists = [];
        foreach ($tags as $tag) {
            $taggables_count = $tag->taggables->count();
            array_push($tag_lists, [
                'id' => $tag->id,
                'name' => $tag->name,
                'count' => $taggables_count
            ]);
        }
        $tender_post_type = config('b2b.TENDER_POST_TYPE');
        $filter_options = [
            'categories' => $categories,
            'post_type' => array_values($tender_post_type),
            'popular_tags' => collect($tag_lists)->sortByDesc('count')->take(10)->values(),
        ];
        return api_response($request, $filter_options, 200, ['filter_options' => $filter_options]);
    }

    /**
     * @param Request $request
     * @param ProcurementFilterRequest $procurement_filter_request
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function tenders(Request $request, ProcurementFilterRequest $procurement_filter_request, TimeFrame $time_frame)
    {
        if ($request->has('min_price') && $request->has('max_price')) $procurement_filter_request->setMinPrice($request->min_price)->setMaxPrice($request->max_price);
        if ($request->has('start_date') && $request->has('end_date')) {
            $time_frame->set(Carbon::parse($request->start_date), Carbon::parse($request->end_date));
            $start_date = $time_frame->getArray()[0];
            $end_date = $time_frame->getArray()[1];
            $procurement_filter_request->setStartDate($start_date)->setEndDate($end_date);
        }
        if ($request->has('tag')) $procurement_filter_request->setTagsId(json_decode($request->tag));
        if ($request->has('category') && $request->category != 'all') $procurement_filter_request->setCategoriesId(json_decode($request->category));
        if ($request->has('shared_to')) $procurement_filter_request->setSharedTo(json_decode($request->shared_to));
        if ($request->has('q')) $procurement_filter_request->setSearchQuery($request->q);

        $procurements = $this->procurementRepository->getProcurementFilterBy($procurement_filter_request);

        $procurements = $procurements->filter(function ($procurement) {
            $number_of_participants = $procurement->number_of_participants;
            $number_of_bids = $procurement->bids()->count();
            if ($number_of_participants) return $number_of_participants != $number_of_bids;
            return $procurement;
        });

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurements, new TenderTransformer());
        $procurements = $manager->createData($resource)->toArray()['data'];

        if ($request->has('sort'))
            $procurements = $this->procurementOrderBy($procurements, $request->sort)->values()->toArray();

        $procurements_with_pagination_data = $this->paginateCollection(collect($procurements), 10);

        return api_response($request, null, 200, ['tenders' => $procurements_with_pagination_data]);
    }

    /**
     * CUSTOM PAGINATION FOR COLLECTION.
     *
     * @param $collection
     * @param $perPage
     * @param string $pageName
     * @param null $fragment
     * @return LengthAwarePaginator
     */
    private function paginateCollection($collection, $perPage, $pageName = 'page', $fragment = null)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage);
        parse_str(request()->getQueryString(), $query);
        unset($query[$pageName]);
        return new LengthAwarePaginator(
            $currentPageItems->values(),
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'pageName' => $pageName,
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $query,
                'fragment' => $fragment
            ]
        );
    }

    /**
     * @param Request $request
     * @param Procurement $tender
     * @return JsonResponse
     */
    public function tenderShow(Request $request, $tender)
    {
        $procurement = $this->procurementRepository->find($tender);
        if (!$procurement) return api_response($request, null, 404, ['message' => 'Tender not Found']);

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($procurement, new TenderDetailsTransformer(true));
        $procurement = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['tender' => $procurement]);
    }

    /**
     * @param $procurements
     * @param string $sort
     * @return \Illuminate\Support\Collection
     */
    private function procurementOrderBy($procurements, $sort = 'asc')
    {
        $sort_by = ($sort == 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($procurements)->$sort_by(function ($procurement, $key) {
            return strtoupper($procurement['id']);
        });
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @return JsonResponse
     */
    public function show($business, $procurement, Request $request)
    {
        /** @var Procurement $procurement */
        $procurement = $this->procurementRepository->find($procurement);
        if (!$procurement) return api_response($request, null, 404, ["message" => "Not found."]);
        if ($procurement->owner instanceof Business && ($procurement->owner->id != $request->business->id))
            return api_response($request, null, 404, ["message" => "Not found."]);

        $share = $this->getShareInformation($procurement);
        $number_of_participants = config('b2b.NUMBER_OF_PARTICIPANTS');

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($procurement, new ProcurementDetailsTransformer());
        $procurement = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, [
            'procurement' => $procurement,
            'number_of_participants' => $number_of_participants,
            'share' => $share
        ]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updateGeneral($business, $procurement, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'number_of_participants' => 'sometimes|required|numeric',
            'procurement_start_date' => 'sometimes|required|date_format:Y-m-d',
            'procurement_end_date' => 'sometimes|required|date_format:Y-m-d',
        ]);

        $procurement = $this->procurementRepository->find($procurement);
        if (!$procurement) return api_response($request, null, 404, ["message" => "Not found."]);

        $this->procurementRequestHandler->setNumberOfParticipants($request->number_of_participants)
            ->setProcurementStartDate($request->procurement_start_date)
            ->setProcurementEndDate($request->procurement_end_date)
            ->setPaymentOptions($request->payment_options)
            ->setCategory($request->category_id)
            ->setTags($request->tags);
        $updater->setRequestHandler($this->procurementRequestHandler)->setProcurement($procurement)->update();

        return api_response($request, null, 200, ["message" => "Successful"]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param BasicInfoUpdater $updater
     * @return JsonResponse
     */
    public function updateBasic($business, $procurement, Request $request, BasicInfoUpdater $updater)
    {
        $this->validate($request, [
            'status' => 'string',
            'title' => 'string',
            'estimated_price' => 'string',
            'last_date_of_submission' => 'date_format:Y-m-d'
        ]);

        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $procurement = $this->procurementRepository->find($procurement);
        if (!$procurement) return api_response($request, null, 404, ["message" => "Not found."]);

        if ($request->status === 'Draft') {
            $updater->setProcurement($procurement)
                ->setTitle($request->title)
                ->setBudget($request->estimated_price)
                ->setLastDateOfSubmission($request->last_date_of_submission)
                ->updateForDraft();
        }
        if ($request->status === 'Open') {
            $updater->setProcurement($procurement)
                ->setLastDateOfSubmission($request->last_date_of_submission)
                ->updateForOpen();
        }

        return api_response($request, null, 200, ["message" => "Successful"]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param AttachmentUpdater $updater
     * @return JsonResponse
     */
    public function updateAttachments($business, $procurement, Request $request, AttachmentUpdater $updater)
    {
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $procurement = $this->procurementRepository->find($procurement);
        if (!$procurement) return api_response($request, null, 404, ["message" => "Not found."]);

        if ($request->added_documents && is_array($request->added_documents)) {
            $updater->setAttachmentsForAdd($request->added_documents)->setProcurement($procurement)
                ->setCreatedBy($request->manager_member)->addAttachments();
        }
        if (!is_null($request->deleted_documents)) {
            $deleted_documents = json_decode($request->deleted_documents);
            $updater->setAttachmentsForDelete($deleted_documents)->deleteAttachments();
        }

        return api_response($request, null, 200, ["message" => "Successful"]);
    }

    public function updateDescription($business, $procurement, Request $request, DescriptionUpdater $updater)
    {
        $this->validate($request, [
            'description' => 'required|string'
        ]);

        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $procurement = $this->procurementRepository->find($procurement);
        if (!$procurement) return api_response($request, null, 404, ["message" => "Not found."]);

        $updater->setProcurement($procurement)->setDescription($request->description)->update();

        return api_response($request, null, 200, ["message" => "Successful"]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updateItem($business, $procurement, Request $request, Updater $updater)
    {
        $this->validate($request, [
            'item' => 'required|string',
            'item_type' => 'required|string|in:price_quotation,technical_evaluation,company_evaluation',
        ]);
        $this->setModifier($request->manager_member);
        $procurement = $this->procurementRepository->find($procurement);
        if (!$procurement) return api_response($request, null, 404, ["message" => "Not found."]);
        $updater->setProcurement($procurement)->itemFieldsUpdate($request);

        return api_response($request, null, 200, ["message" => "Successful"]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Sms $sms
     * @param BitlyLinkShort $bitly_link
     * @param ProcurementRepositoryInterface $procurementRepository
     * @param ProcurementInvitationRepositoryInterface $procurement_invitation_repository
     * @return JsonResponse
     */
    public function sendInvitation($business, $procurement, Request $request, Sms $sms,
                                   BitlyLinkShort $bitly_link,
                                   ProcurementRepositoryInterface $procurementRepository,
                                   ProcurementInvitationRepositoryInterface $procurement_invitation_repository)
    {
        $this->validate($request, ['partners' => 'required|string']);
        $partners = Partner::whereIn('id', json_decode($request->partners))->get();
        $business = $request->business;
        $this->setModifier($request->manager_member);

        $procurement = $procurementRepository->find($procurement);

        foreach ($partners as $partner) {
            /** @var Partner $partner */
            $creator = new ProcurementInvitationCreator($procurement_invitation_repository);
            $procurement_invitation = $creator->setBusinessMember($request->business_member)
                ->setProcurement($procurement)
                ->setPartner($partner);

            if ($creator->hasError()) {
                if ($creator->getErrorCode() == 409) {
                    $this->shootSmsForInvitation($business, $procurement, $bitly_link, $sms, $partner);
                }
                continue;
            }

            $procurement_invitation->create();
            $this->shootSmsForInvitation($business, $procurement, $bitly_link, $sms, $partner);
        }

        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function updateStatus($business, $procurement, Request $request, Creator $creator)
    {
        $procurement_shared_to_options = implode(',', array_column(config('b2b.SHARING_TO'), 'key'));
        $this->validate($request, [
            'is_published' => 'required|integer:in:1,0',
            'sharing_to' => 'sometimes|required|in:' . $procurement_shared_to_options
        ]);

        $procurement = Procurement::find((int)$procurement);
        if (!$procurement) return api_response($request, null, 404);

        if ($request->has('sharing_to')) $creator->setSharingTo($request->sharing_to);
        $creator->setIsPublished($request->is_published)->changeStatus($procurement);

        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function updatePublicationStatus($business, $procurement, Request $request, Creator $creator)
    {
        $publication_status = implode(',', PublicationStatuses::get());
        $this->validate($request, ['publication_status' => 'required|string:in:' . $publication_status]);
        $this->setModifier($request->manager_member);

        $procurement = Procurement::find((int)$procurement);
        if (!$procurement) return api_response($request, null, 404);

        $creator->setPublicationStatus($request->publication_status)->changePublicationStatus($procurement);

        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param ProcurementAdapter $procurement_adapter
     * @param PaymentManager $payment_manager
     * @param ShebaPaymentValidator $payment_validator
     * @param ProcurementRepositoryInterface $procurement_repository
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function clearBills($business, $procurement, Request $request, ProcurementAdapter $procurement_adapter, PaymentManager $payment_manager, ShebaPaymentValidator $payment_validator, ProcurementRepositoryInterface $procurement_repository)
    {
        $this->validate($request, [
            'payment_method' => 'required|in:' . implode(',', AvailableMethods::getBusinessPayments()),
            'emi_month' => 'numeric'
        ]);
        $payment_method = $request->payment_method;
        $procurement = $procurement_repository->find($procurement);
        $payment_validator->setPayableType('procurement')->setPayableTypeId($procurement->id)->setPaymentMethod($payment_method);
        if (!$payment_validator->canInitiatePayment()) return api_response($request, null, 403, ['message' => "Can't send multiple requests within 1 minute."]);
        $payable = $procurement_adapter->setModelForPayable($procurement)->setEmiMonth($request->emi_month)->getPayable();
        $payment = $payment_manager->setMethodName($payment_method)->setPayable($payable)->init();

        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @return JsonResponse
     */
    public function orderTimeline($business, $procurement, Request $request)
    {
        $order_timelines = $this->procurementOrder->setProcurement($procurement)->getBid()->formatTimeline();
        return api_response($request, $order_timelines, 200, ['timelines' => $order_timelines]);
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function procurementOrders($business, Request $request)
    {
        $procurement_orders = Procurement::order()->with([
            'bids' => function ($q) {
                $q->select('id', 'procurement_id', 'bidder_id', 'bidder_type', 'status', 'price')->with([
                    'bidder' => function ($q) {
                        $q->with(['resources' => function ($q) {
                            $q->select('resources.id', 'profile_id')->with('profile');
                        }]);
                    }]);
            }
        ])->where('owner_id', (int)$business)->orderBy('id', 'DESC');

        $is_order_available = $procurement_orders->count() > 0 ? 1 : 0;

        $start_date = $request->has('start_date') ? $request->start_date : null;
        $end_date = $request->has('end_date') ? $request->end_date : null;
        if ($start_date && $end_date) {
            $procurement_orders->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        }
        list($procurement_orders, $total_orders) = $this->procurementOrder->orders($procurement_orders, $request);

        if (count($procurement_orders) > 0) return api_response($request, $procurement_orders, 200, [
            'procurement_orders' => $procurement_orders, 'total_orders' => $total_orders, 'is_order_available' => $is_order_available
        ]); else return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param BidRepositoryInterface $bid_repository
     * @return JsonResponse
     */
    public function showProcurementOrder($business, $procurement, $bid, Request $request, BidRepositoryInterface $bid_repository)
    {
        $bid = $bid_repository->find((int)$bid);
        if (!$bid) return api_response($request, null, 404);
        $rfq_order_details = $this->procurementOrder->setProcurement($procurement)->setBid($bid)->orderDetails();
        return api_response($request, null, 200, ['order_details' => $rfq_order_details]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @return JsonResponse
     */
    public function orderBill($business, $procurement, Request $request)
    {
        $procurement = $this->procurementRepository->find((int)$procurement);
        $procurement->calculate();
        $rfq_order_bill['total_price'] = $procurement->getActiveBid()->price;
        $rfq_order_bill['paid'] = $procurement->paid;
        $rfq_order_bill['due'] = $procurement->due;
        return api_response($request, $rfq_order_bill, 200, ['rfq_order_bill' => $rfq_order_bill]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @return JsonResponse
     */
    public function invitedPartners($business, $procurement, Request $request)
    {
        $procurement = $this->procurementRepository->find((int)$procurement);
        $procurement->load(['bids' => function ($q) {
            $q->select('id', 'procurement_id', 'bidder_id', 'bidder_type');
        }, 'invitations' => function ($q) {
            $q->with([
                'partner' => function ($q) {
                    $q->select('id', 'name', 'logo');
                }
            ]);
        }]);
        $is_invitation_available = $procurement->invitations()->count() ? 1 : 0;
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurement->invitations, new ProcurementInvitationListTransformer($procurement));
        $invited_partners = $manager->createData($resource)->toArray()['data'];

        if ($request->has('sort_by_name')) $invited_partners = $this->sortByName($invited_partners, $request->sort_by_name)->values();
        if ($request->has('sort_by_date')) $invited_partners = $this->sortByDate($invited_partners, $request->sort_by_date)->values();
        if ($request->has('sort_by_status')) $invited_partners = $this->sortByStatus($invited_partners, $request->sort_by_status)->values();

        $procurement_status = ProcurementStatusCalculator::resolveStatus($procurement);

        return api_response($request, null, 200, [
            'invited_partners' => $invited_partners,
            'is_invitation_available' => $is_invitation_available,
            'procurement_status' => $procurement_status
        ]);
    }

    /**
     * @param $invited_partners
     * @param string $sort
     * @return mixed
     */
    private function sortByName($invited_partners, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($invited_partners)->$sort_by(function ($invited_partner) {
            return strtoupper($invited_partner['vendor']['name']);
        });
    }

    /**
     * @param $invited_partners
     * @param string $sort
     * @return mixed
     */
    private function sortByDate($invited_partners, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($invited_partners)->$sort_by(function ($invited_partner) {
            return strtoupper($invited_partner['invited_on']);
        });
    }

    /**
     * @param $invited_partners
     * @param string $sort
     * @return mixed
     */
    private function sortByStatus($invited_partners, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($invited_partners)->$sort_by(function ($invited_partner) {
            return strtoupper($invited_partner['status']);
        });
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function downloadPdf(Request $request)
    {
        $business = $request->business;
        $procurement = Procurement::find($request->procurement);
        $price_quotation = $procurement->items->where('type', 'price_quotation')->first();
        $technical_evaluation = $procurement->items->where('type', 'technical_evaluation')->first();
        $company_evaluation = $procurement->items->where('type', 'company_evaluation')->first();

        $procurement_details = [
            'id' => $procurement->id,
            'title' => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20),
            'status' => $procurement->status,
            'type' => $procurement->type,
            'long_description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'start_date' => $procurement->procurement_start_date->format('d/m/y'),
            'published_at' => ($procurement->publication_status == PublicationStatuses::PUBLISHED) ? $procurement->published_at->format('d/m/y') : null,
            'end_date' => $procurement->procurement_end_date->format('d/m/y'),
            'number_of_participants' => $procurement->number_of_participants,
            'last_date_of_submission' => $procurement->last_date_of_submission->format('Y-m-d'),
            'payment_options' => $procurement->payment_options,
            'created_at' => $procurement->created_at->format('d M, Y'),
            'business' => [
                'name' => $business->name,
                'logo' => $business->logo,
                'address' => $business->address,
            ],
            'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
            'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields : null : null,
            'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields : null : null,
        ];

        // return view('pdfs.procurement_details', compact('procurement_details'));
        return App::make('dompdf.wrapper')
            ->loadView('pdfs.procurement_details', compact('procurement_details'))
            ->download('tender-details.pdf');
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param WorkOrderDataGenerator $data_generator
     * @return JsonResponse
     */
    public function workOrder($business, $procurement, $bid, Request $request, WorkOrderDataGenerator $data_generator)
    {
        $business = $request->business;
        $bid = Bid::findOrFail((int)$bid);
        $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();
        return api_response($request, null, 200, ['work_order' => $work_order]);
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param WorkOrderDataGenerator $data_generator
     * @return void
     */
    public function downloadWorkOrder($business, $procurement, $bid, Request $request, WorkOrderDataGenerator $data_generator)
    {
        $business = $request->business;
        $bid = Bid::findOrFail((int)$bid);
        $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();
        #return view('pdfs.work_order', compact('work_order'));
        return App::make('dompdf.wrapper')->loadView('pdfs.work_order', compact('work_order'))->download('work_order.pdf');

    }

    /**
     * @param Business $business
     * @param Procurement $procurement
     * @param BitlyLinkShort $bitly_link
     * @param Sms $sms
     * @param Partner $partner
     */
    private function shootSmsForInvitation(Business $business, Procurement $procurement, BitlyLinkShort $bitly_link, Sms $sms, Partner $partner)
    {
        $url = config('sheba.business_url') . "/tender/list/$procurement->id";
        $sms->setFeatureType(FeatureType::PROCUREMENT)
            ->setBusinessType(BusinessType::B2B)
            ->shoot($partner->getManagerMobile(), "You have been invited to serve $business->name. Now go to this link-" . $bitly_link->shortUrl($url));
    }

    /**
     * @param $tender
     * @param Request $request
     * @param BidCreator $creator
     * @param BidRepositoryInterface $bid_repository
     * @param ProcurementInvitationRepositoryInterface $procurement_invitation_repo
     * @return JsonResponse
     * @throws Exception
     */
    public function tenderProposalStore($tender, Request $request,
                                        BidCreator $creator,
                                        BidRepositoryInterface $bid_repository,
                                        ProcurementInvitationRepositoryInterface $procurement_invitation_repo)
    {
        $this->validate($request, [
            'price' => 'sometimes|numeric',
            'proposal' => 'required|string',
            'company_name' => 'required|string',
            'company_phone' => 'required|string',
            'name' => 'required|string',
            'status' => 'required|string'
        ]);

        /** @var Procurement $procurement */
        $procurement = $this->procurementRepository->find($tender);
        $partner = $this->getPartner($request);
        $shared_to_statuses = config('b2b.SHARING_TO');

        if ($this->isVerifiedOnlyTender($procurement, $partner, $shared_to_statuses))
            return api_response($request, null, 420, ['reason' => 'verification']);

        if ($this->isInviteOnlyTender($procurement, $partner, $shared_to_statuses, $procurement_invitation_repo))
            return api_response($request, null, 420, ['reason' => 'invite_only']);

        $this->setModifier($partner);

        $bid = $this->getBid($bid_repository, $tender, $partner);
        if ($bid) {
            if ($bid->status == BidStatuses::SENT) $bid->delete();
            else return api_response($request, null, 420, ['reason' => 'already_submitted', 'message' => 'You already submit a proposal on this tender and those proposal on a working stage']);
        }

        $procurement->load('items.fields');

        $field_results = $this->getFieldResultBy($procurement, $request);
        if ($field_results instanceof JsonResponse) {
            $json_response = $field_results->getData();
            return api_response($request, null, $json_response->code, ['message' => $json_response->message]);
        }

        $creator->setBidder($partner)
            ->setProcurement($procurement)
            ->setStatus($request->status)
            ->setProposal($request->proposal)
            ->setFieldResults($field_results)
            ->setPrice($request->price)
            ->setCreatedBy($this->resource);

        if ($request->attachments && is_array($request->attachments))
            $creator->setAttachments($request->attachments);

        $creator->create();

        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return Partner
     */
    private function getPartner(Request $request)
    {
        /** @var Profile $profile */
        $profile = $this->profileRepository->checkExistingProfile($request->company_phone, $request->email);

        if (!$profile || !$profile->resource) {
            $this->resourceCreator->setResourceCreateRequest($this->resourceCreateRequest)->setData($this->formatProfileSpecificData($request));
            $this->resource = $this->resourceCreator->create();
        } else {
            $this->resource = $profile->resource;
        }

        $request = $this->partnerCreateRequest
            ->setName($request->company_name)
            ->setMobile($request->company_phone)
            ->setEmail($request->email);

        if (!$this->resource->firstPartner()) {
            $partner = $this->partnerCreator->setPartnerCreateRequest($request)->create();
            $partner->subscribe(config('sheba.partner_lite_packages_id'), 'monthly');
            $partner->resources()->save($this->resource, ['resource_type' => 'Admin']);
        } else {
            $partner = $this->resource->firstPartner();
        }

        return $partner;
    }

    /**
     * @param $request
     * @return array
     */
    private function formatProfileSpecificData($request)
    {
        return [
            'name' => $request->name,
            'mobile' => $request->company_phone,
            'email' => $request->email,
            'alternate_contact' => null
        ];
    }

    /**
     * @param BidRepositoryInterface $bid_repository
     * @param $tender
     * @param $partner
     * @return Bid|null
     */
    private function getBid(BidRepositoryInterface $bid_repository, $tender, $partner)
    {
        return $bid_repository->where('procurement_id', $tender)
            ->where('bidder_type', 'like', '%Partner')
            ->where('bidder_id', $partner->id)
            ->first();
    }

    /**
     * @param $field_source
     * @param $request
     * @return array|JsonResponse
     */
    private function getFieldResultBy($field_source, $request)
    {
        $items = collect(json_decode($request->items));
        $field_results = [];
        foreach ($field_source->items as $procurement_item) {
            $item = $items->where('id', $procurement_item->id)->first();
            foreach ($procurement_item->fields as $item_field) {
                $variables = json_decode($item_field->variables);
                $required = (int)$variables->is_required;

                if ($required && !$item) return api_response($request, null, 400, ['message' => $procurement_item->type . ' missing']);
                elseif (!$required && !$item) continue;

                $fields = collect($item->fields);
                $field = $fields->where('id', $item_field->id)->first();
                array_push($field_results, $field);
            }
        }

        return $field_results;
    }

    /**
     * @param $tender
     * @param Request $request
     * @return JsonResponse
     */
    public function tenderProposalEdit($tender, Request $request)
    {
        $procurement = $this->procurementRepository->find($tender);
        if (!$procurement) return api_response($request, null, 404, ['message' => 'Tender not Found']);
        if ($procurement->status != StatusCalculator::IS_PENDING)
            return api_response($request, null, 422, ['message' => 'Vendor already hired']);

        $price_quotation_fields = $this->generateItemData($procurement, 'price_quotation');
        $technical_evaluation_fields = $this->generateItemData($procurement, 'technical_evaluation');
        $company_evaluation_fields = $this->generateItemData($procurement, 'company_evaluation');

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($procurement, new TenderDetailsTransformer(true));
        $procurement = $fractal->createData($resource)->toArray()['data'];

        $procurement['price_quotation'] = $price_quotation_fields;
        $procurement['company_evaluation'] = $company_evaluation_fields;
        $procurement['technical_evaluation'] = $technical_evaluation_fields;

        return api_response($request, null, 200, ['tender' => $procurement]);
    }

    /**
     * @param $procurement
     * @param $type
     * @return array|null
     */
    public function generateItemData($procurement, $type)
    {
        $type_data = $procurement->items->where('type', $type)->first();
        return $type_data ? $type_data->fields->map(function ($field) use ($procurement) {
            return [
                'id' => $field->bid_item_id ? $field->bid_item_id : $field->procurement_item_id,
                'field_id' => $field->id,
                'title' => $field->title,
                'input_type' => $field->input_type,
                'short_description' => $field->short_description,
                'long_description' => $field->long_description,
                'unit' => $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 'n/a' : 'n/a',
                'options' => $field->variables ? json_decode($field->variables)->options ? json_decode($field->variables)->options : 'n/a' : 'n/a',
                'result' => $field->bid_item_id && $procurement->status === 'pending' ? $field->input_type === 'checkbox' ? json_decode($field->result) : $field->result : $field->result
            ];
        }) : null;
    }

    /**
     * @param Procurement $procurement
     * @param Partner $partner
     * @param $shared_to_statuses
     * @return bool
     */
    private function isVerifiedOnlyTender(Procurement $procurement, Partner $partner, $shared_to_statuses)
    {
        if ($procurement->shared_to != ($shared_to_statuses['verified'])['key']) return false;
        if ($partner->status == PartnerStatuses::VERIFIED) return false;

        return true;
    }

    /**
     * @param Procurement $procurement
     * @param Partner $partner
     * @param $shared_to_statuses
     * @param ProcurementInvitationRepositoryInterface $procurement_invitation_repo
     * @return bool
     */
    private function isInviteOnlyTender(Procurement $procurement, Partner $partner, $shared_to_statuses, ProcurementInvitationRepositoryInterface $procurement_invitation_repo)
    {
        if ($procurement->shared_to != ($shared_to_statuses['own_listed'])['key']) return false;

        $procurement_invitation = $procurement_invitation_repo->findByProcurementPartner($procurement, $partner);
        if ($procurement_invitation) return false;

        return true;
    }

    /**
     * @param Request $request
     * @param ProcurementFilterRequest $procurement_filter_request
     * @return JsonResponse
     */
    public function landings(Request $request, ProcurementFilterRequest $procurement_filter_request)
    {
        $data = [];
        $categories = [];
        $data['statistics'] = ['tenders' => 1200, 'suppliers' => 400, 'quotations' => 4900, 'avg_duration' => 3, 'industries' => 99];
        $categories_id = config('sheba.tender_landing_categories_id');
        Category::whereIn('id', $categories_id)->get()->each(function ($category) use (&$categories) {
            $categories[$category->id] = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon_svg
            ];
        });
        $data['categories'] = array_values($categories);

        $procurement_filter_request->setLimit(10);
        $procurements = $this->procurementRepository->getProcurementWhereTitleBudgetNotNull($procurement_filter_request);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurements, new TenderMinimalTransformer());
        $procurements = $manager->createData($resource)->toArray()['data'];
        $data['tenders'] = $procurements;

        return api_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param $tender
     * @param $proposal
     * @param Request $request
     * @param BidRepositoryInterface $bid_repo
     * @return JsonResponse
     */
    public function proposalDetail($tender, $proposal, Request $request, BidRepositoryInterface $bid_repo)
    {
        $proposal = $bid_repo->find($proposal);
        if (!$proposal) return api_response($request, null, 404);
        if ($proposal->procurement_id != (int)$tender) return api_response($request, null, 404);
        $procurement = $this->procurementRepository->find($tender);
        if (!$this->isTenderProposalAcceptOrRejectable($procurement, $proposal))
            return api_response($request, null, 422);

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($procurement, new ProposalDetailsTransformer($proposal));
        $procurement = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $procurement]);
    }

    /**
     * @param $procurement
     * @return array
     */
    private function getShareInformation($procurement)
    {
        return [
            'link' => config('sheba.business_url') . "/tender/list/$procurement->id",
            'title' => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20),
            'description' => substr($procurement->long_description, 0, 30)
        ];
    }

    /**
     * @param $procurement
     * @param $proposal
     * @return bool
     */
    private function isTenderProposalAcceptOrRejectable($procurement, $proposal)
    {
        return $procurement->status == Statuses::PENDING && BidStatuses::isAcceptOrRejectable($proposal->status);
    }
}
