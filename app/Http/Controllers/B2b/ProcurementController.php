<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Bid;
use App\Models\Business;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Procurement;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\Tag;
use App\Sheba\Bitly\BitlyLinkShort;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\Bid\Updater as BidUpdater;
use App\Transformers\AttachmentTransformer;
use App\Transformers\Business\ProcurementListTransformer;
use App\Transformers\Business\TenderDetailsTransformer;
use App\Transformers\Business\TenderMinimalTransformer;
use App\Transformers\Business\TenderTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use ReflectionException;
use Sheba\Business\Bid\Creator as BidCreator;
use Sheba\Business\Procurement\Creator;
use Sheba\Business\Procurement\ProcurementFilterRequest;
use Sheba\Business\Procurement\WorkOrderDataGenerator;
use Sheba\Dal\ProcurementInvitation\Model as ProcurementInvitation;
use Sheba\Dal\ProcurementInvitation\ProcurementInvitationRepositoryInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Logs\ErrorLog;
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
use Sheba\Repositories\Business\ProcurementRepository;
use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\Repositories\ProfileRepository;
use Sheba\Resource\ResourceCreator;
use Sheba\Sms\Sms;
use Sheba\Business\ProcurementInvitation\Creator as ProcurementInvitationCreator;
use Throwable;

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

    /**
     * ProcurementController constructor.
     *
     * @param ProcurementRepositoryInterface $procurement_repository
     * @param ProfileRepository $profile_repo
     * @param ResourceCreator $resource_creator
     * @param PartnerCreator $partner_creator
     * @param PartnerCreateRequest $partner_create_request
     * @param ProcurementFilterRequest $procurement_filter_request
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repository,
                                ProfileRepository $profile_repo,
                                ResourceCreator $resource_creator,
                                PartnerCreator $partner_creator,
                                PartnerCreateRequest $partner_create_request,
                                ProcurementFilterRequest $procurement_filter_request)
    {
        $this->procurementRepository = $procurement_repository;
        $this->profileRepository = $profile_repo;
        $this->resourceCreator = $resource_creator;
        $this->partnerCreator = $partner_creator;
        $this->partnerCreateRequest = $partner_create_request;
        $this->procurementFilterRequest = $procurement_filter_request;
    }

    public function create(Request $request)
    {
        $categories = Category::child()->published()->publishedForB2B()->select('id', 'name')->get()->toArray();
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
            return api_response($request, null, 403);

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

        return api_response($request, $procurement, 200, ['id' => $procurement->id]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param AccessControl $access_control
     * @return JsonResponse
     */
    public function index($business, Request $request, AccessControl $access_control)
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
            ->select(['id', 'title', 'long_description', 'status', 'last_date_of_submission', 'created_at', 'is_published'])
            ->orderBy('id', 'desc');

        if ($request->has('status') && $request->status != 'all') $procurements = $this->procurementRepository->filterWithStatus($request->status);
        $start_date = $request->has('start_date') ? $request->start_date : null;
        $end_date = $request->has('end_date') ? $request->end_date : null;
        if ($start_date && $end_date) $procurements = $this->procurementRepository->filterWithCreatedAt($start_date, $end_date);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Collection($procurements->get(), new ProcurementListTransformer());
        $procurements = $manager->createData($resource)->toArray()['data'];

        if ($request->has('search')) $procurements = $this->searchByTitle($procurements, $request)->values();
        if ($request->has('sort_by_id')) $procurements = $this->sortById($procurements, $request->sort_by_id)->values();
        if ($request->has('sort_by_title')) $procurements = $this->sortByTitle($procurements, $request->sort_by_title)->values();
        if ($request->has('sort_by_created_at')) $procurements = $this->sortByCreatedAt($procurements, $request->sort_by_created_at)->values();
        $total_procurement = count($procurements);
        if ($request->has('limit')) $procurements = collect($procurements)->splice($offset, $limit);
        if (count($procurements) > 0) return api_response($request, $procurements, 200, [
            'procurements' => $procurements, 'total_procurement' => $total_procurement
        ]); else return api_response($request, null, 404);
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
        if ($request->has('shared_to')) $procurement_filter_request->setSharedTo($request->shared_to);
        if ($request->has('q')) $procurement_filter_request->setSearchQuery($request->q);

        $procurements = $this->procurementRepository->getProcurementFilterBy($procurement_filter_request);
        // list($offset, $limit) = calculatePagination($request);
        // $procurements = $procurements->skip($offset)->limit($limit);
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
            $currentPageItems,
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
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        try {
            $procurement = Procurement::find($request->procurement);

            if (is_null($procurement)) {
                return api_response($request, null, 404, ["message" => "Not found."]);
            } else {
                $price_quotation = $procurement->items->where('type', 'price_quotation')->first();
                $technical_evaluation = $procurement->items->where('type', 'technical_evaluation')->first();
                $company_evaluation = $procurement->items->where('type', 'company_evaluation')->first();

                $procurement_details = [
                    'id' => $procurement->id,
                    'title' => $procurement->title,
                    'status' => $procurement->status,
                    'long_description' => $procurement->long_description,
                    'labels' => $procurement->getTagNamesAttribute()->toArray(),
                    'start_date' => Carbon::parse($procurement->procurement_start_date)->format('d/m/y'),
                    'published_at' => $procurement->is_published ? Carbon::parse($procurement->published_at)->format('d/m/y') : null,
                    'end_date' => Carbon::parse($procurement->procurement_end_date)->format('d/m/y'),
                    'number_of_participants' => $procurement->number_of_participants,
                    'last_date_of_submission' => Carbon::parse($procurement->last_date_of_submission)->format('Y-m-d'),
                    'payment_options' => $procurement->payment_options,
                    'created_at' => Carbon::parse($procurement->created_at)->format('d/m/y'),
                    'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
                    'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null,
                    'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null,
                    'attachments' => $procurement->attachments->map(function (Attachment $attachment) {
                        return (new AttachmentTransformer())->transform($attachment);
                    })->toArray()
                ];
                return api_response($request, $procurement_details, 200, ['procurements' => $procurement_details]);
            }
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateGeneral(Request $request)
    {
        try {
            $this->validate($request, [
                'number_of_participants' => 'required|numeric', 'last_date_of_submission' => 'required|date_format:Y-m-d', 'procurement_start_date' => 'date_format:Y-m-d', 'payment_options' => 'string'
            ]);

            $procurement = Procurement::find($request->procurement);

            if (is_null($procurement)) {
                return api_response($request, null, 404, ["message" => "Not found."]);
            } else {
                $procurement->number_of_participants = $request->number_of_participants;
                $procurement->last_date_of_submission = $request->last_date_of_submission;
                if ($request->procurement_start_date) $procurement->procurement_start_date = $request->procurement_start_date;
                if ($request->payment_options) $procurement->payment_options = $request->payment_options;

                $procurement->save();
                return api_response($request, null, 200, ["message" => "Successful"]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Sms $sms
     * @param ErrorLog $errorLog
     * @param BitlyLinkShort $bitly_link
     * @param ProcurementRepositoryInterface $procurementRepository
     * @param ProcurementInvitationRepositoryInterface $procurement_invitation_repository
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @return bool|JsonResponse
     */
    public function sendInvitation($business, $procurement, Request $request, Sms $sms,
                                   ErrorLog $errorLog,
                                   BitlyLinkShort $bitly_link,
                                   ProcurementRepositoryInterface $procurementRepository,
                                   ProcurementInvitationRepositoryInterface $procurement_invitation_repository,
                                   BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->validate($request, [
            'partners' => 'required|string',
        ]);
        $partners = Partner::whereIn('id', json_decode($request->partners))->get();
        $business = $request->business;
        $procurement = $procurementRepository->find($procurement);

        foreach ($partners as $partner) {
            /** @var Partner $partner */
            $creator = new ProcurementInvitationCreator($procurement_invitation_repository);
            $procurement_invitation = $creator->setBusinessMember($request->business_member)->setProcurement($procurement)->setPartner($partner);
            if ($creator->hasError()) {
                if ($creator->getErrorCode() == 409) {
                    $procurement_invitation = $procurement_invitation->getProcurementInvitation();
                    $this->shootSmsForInvitation($business, $procurement_invitation, $bitly_link, $sms, $partner);
                }
                continue;
            }

            $procurement_invitation = $procurement_invitation->create();
            $this->shootSmsForInvitation($business, $procurement_invitation, $bitly_link, $sms, $partner);
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
     * @param Creator $creator
     * @return JsonResponse
     */
    public function orderTimeline($business, $procurement, Request $request, Creator $creator)
    {
        try {
            $procurement = $creator->getProcurement($procurement)->getBid();
            $order_timelines = $creator->formatTimeline();
            return api_response($request, $order_timelines, 200, ['timelines' => $order_timelines]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function procurementOrders($business, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $procurements = Procurement::order()->with([
                'bids' => function ($q) {
                    $q->select('id', 'procurement_id', 'bidder_id', 'bidder_type', 'status', 'price');
                }
            ])->where('owner_id', (int)$business)->orderBy('id', 'DESC');

            $total_procurement = $procurements->get()->count();

            if ($request->has('status') && $request->status != 'all') {
                $procurements = $procurements->where('status', $request->status);
            }

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $procurements->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $procurements = $procurements->skip($offset)->limit($limit)->get();
            $rfq_order_lists = [];
            foreach ($procurements as $procurement) {
                $bid = $procurement->getActiveBid() ? $procurement->getActiveBid() : null;
                array_push($rfq_order_lists, [
                    'procurement_id' => $procurement->id, 'procurement_title' => $procurement->title, 'procurement_status' => $procurement->status, 'created_at' => $procurement->created_at->format('d/m/y'), 'color' => constants('PROCUREMENT_ORDER_STATUSES_COLOR')[$procurement->status], 'bid_id' => $bid ? $bid->id : null, 'price' => $bid ? $bid->price : null, 'vendor' => [
                        'name' => $bid ? $bid->bidder->name : null
                    ]
                ]);
            }

            return api_response($request, $rfq_order_lists, 200, [
                'rfq_order_lists' => $rfq_order_lists, 'total_procurement' => $total_procurement,
            ]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function showProcurementOrder($business, $procurement, $bid, Request $request, Creator $creator)
    {
        try {
            $bid = Bid::findOrFail((int)$bid);
            $rfq_order_details = $creator->getProcurement($procurement)->setBid($bid)->formatData();
            return api_response($request, null, 200, ['order_details' => $rfq_order_details]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function orderBill($business, $procurement, Request $request, Creator $creator)
    {
        try {
            $procurement = Procurement::findOrFail((int)$procurement);
            $procurement->calculate();
            $rfq_order_bill['total_price'] = $procurement->getActiveBid()->price;
            $rfq_order_bill['paid'] = $procurement->paid;
            $rfq_order_bill['due'] = $procurement->due;
            return api_response($request, $rfq_order_bill, 200, ['rfq_order_bill' => $rfq_order_bill]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function downloadPdf(Request $request)
    {
        $procurement = Procurement::find($request->procurement);
        $price_quotation = $procurement->items->where('type', 'price_quotation')->first();
        $technical_evaluation = $procurement->items->where('type', 'technical_evaluation')->first();
        $company_evaluation = $procurement->items->where('type', 'company_evaluation')->first();

        $procurement_details = [
            'id' => $procurement->id,
            'title' => $procurement->title,
            'status' => $procurement->status,
            'long_description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'start_date' => Carbon::parse($procurement->procurement_start_date)->format('d/m/y'),
            'published_at' => $procurement->is_published ? Carbon::parse($procurement->published_at)->format('d/m/y') : null,
            'end_date' => Carbon::parse($procurement->procurement_end_date)->format('d/m/y'),
            'number_of_participants' => $procurement->number_of_participants,
            'last_date_of_submission' => Carbon::parse($procurement->last_date_of_submission)->format('Y-m-d'),
            'payment_options' => $procurement->payment_options,
            'created_at' => Carbon::parse($procurement->created_at)->format('d/m/y'),
            'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
            'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields : null : null,
            'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields : null : null,
        ];

        return App::make('dompdf.wrapper')
            ->loadView('pdfs.procurement_details', compact('procurement_details'))
            ->download("procurement_details.pdf");
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
        try {
            $business = $request->business;
            $bid = Bid::findOrFail((int)$bid);
            $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();
            return api_response($request, null, 200, ['work_order' => $work_order]);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $procurement
     * @param $bid
     * @param Request $request
     * @param WorkOrderDataGenerator $data_generator
     * @return JsonResponse
     */
    public function downloadWorkOrder($business, $procurement, $bid, Request $request, WorkOrderDataGenerator $data_generator)
    {
        $business = $request->business;
        $bid = Bid::findOrFail((int)$bid);
        $work_order = $data_generator->setBusiness($business)->setProcurement($procurement)->setBid($bid)->get();

        return App::make('dompdf.wrapper')
            ->loadView('pdfs.work_order', compact('work_order'))
            ->download('work_order.pdf');

    }

    /**
     * @param Business $business
     * @param ProcurementInvitation $procurement_invitation
     * @param BitlyLinkShort $bitly_link
     * @param Sms $sms
     * @param Partner $partner
     */
    private function shootSmsForInvitation(Business $business, ProcurementInvitation $procurement_invitation, BitlyLinkShort $bitly_link, Sms $sms, Partner $partner)
    {
        $url = config('sheba.partners_url') . "/v3/rfq-invitations/$procurement_invitation->id";
        $sms->shoot($partner->getManagerMobile(), "You have been invited to serve $business->name. Now go to this link-" . $bitly_link->shortUrl($url));
    }

    /**
     * @param $tender
     * @param Request $request
     * @param BidCreator $creator
     * @param BidUpdater $updater
     * @param ProcurementRepository $procurement_repository
     * @param BidRepositoryInterface $bid_repository
     * @param ProfileRepositoryInterface $profile_repository
     * @param ProcurementInvitationRepositoryInterface $procurement_invitation_repo
     * @return JsonResponse
     * @throws Exception
     */
    public function tenderProposalStore($tender, Request $request,
                                        BidCreator $creator, BidUpdater $updater,
                                        ProcurementRepository $procurement_repository,
                                        BidRepositoryInterface $bid_repository,
                                        ProfileRepositoryInterface $profile_repository,
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
        $procurement = $procurement_repository->find($tender);
        $partner = $this->getPartner($profile_repository, $request);
        $shared_to_statuses = config('b2b.SHARING_TO');

        if ($this->isVerifiedOnlyTender($procurement, $partner, $shared_to_statuses))
            return api_response($request, null, 420, ['reason' => 'verification']);

        if ($this->isInviteOnlyTender($procurement, $partner, $shared_to_statuses, $procurement_invitation_repo))
            return api_response($request, null, 420, ['reason' => 'invite_only']);

        $this->setModifier($partner);

        $bid = $this->getBid($bid_repository, $tender, $partner);
        if ($bid) {
            $field_results = $this->getFieldResultBy($bid, $request);
            if ($field_results instanceof JsonResponse) {
                $json_response = $field_results->getData();
                return api_response($request, null, $json_response->code, ['message' => $json_response->message]);
            }

            $updater->setBid($bid)
                ->setStatus($request->status)
                ->setFieldResults($field_results)
                ->setProposal($request->proposal)
                ->setPrice($request->price)
                ->update();
            
            return api_response($request, null, 200);
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
     * @param ProfileRepositoryInterface $profile_repository
     * @param Request $request
     * @return Partner
     */
    private function getPartner(ProfileRepositoryInterface $profile_repository, Request $request)
    {
        /** @var Profile $profile */
        $profile = $this->profileRepository->checkExistingProfile($request->company_phone, $request->email);

        if (!$profile || !$profile->resource) {
            $this->resourceCreator->setData($this->formatProfileSpecificData($request));
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
     * @param ProcurementRepository $procurement_repository
     * @return JsonResponse
     */
    public function tenderProposalEdit($tender, Request $request, ProcurementRepository $procurement_repository)
    {
        $procurement = $this->procurementRepository->find($tender);
        if (!$procurement) return api_response($request, null, 404, ['message' => 'Tender not Found']);

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
                'id'    => $category->id,
                'name'  => $category->name,
                'icon'  => $category->icon
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
}
