<?php namespace App\Http\Controllers;

use App\Models\Comment;
use Sheba\Complains\ComplainStatusChanger;
use Sheba\Complains\Statuses;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Dal\Accessor\Model as Accessor;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\Accessor\Contract as AccessorRepo;
use Sheba\Dal\ComplainPreset\Contract as ComplainPresetRepo;

use Illuminate\Http\Request;
use Sheba\Dal\Complain\EloquentImplementation as ComplainRepo;
use Sheba\ModificationFields;
use Sheba\Notification\CommentNotification;
use Sheba\Notification\ComplainNotification;
use Sheba\Notification\ComplainNotificationPartner;

class ComplainController extends Controller
{
    use ModificationFields;
    private $accessorRepo;
    private $complainPresetRepo;
    private $complainRepo;

    public function __construct(ComplainRepo $complain, AccessorRepo $accessorRepo, ComplainPresetRepo $complain_preset_repo)
    {
        $this->accessorRepo = $accessorRepo;
        $this->complainPresetRepo = $complain_preset_repo;
        $this->complainRepo = $complain;
    }

    public function index(Request $request, $customer, $job = null)
    {
        ini_set('memory_limit', '2048M');
        $job = $job ?: $request->job_id;
        try {
            $this->validate($request, [
                'for' => 'required|in:customer,partner'
            ]);
            $job = Job::find($job);
            if (!empty($job)) {
                $accessor = $this->accessorRepo->findByNameWithPublishedCategoryAndPresetFilteredByCategoryAndStatus(ucwords($request->for), $job->category_id, $this->getJobStatus($job));
            } else {
                $accessor = $this->accessorRepo->findByNameWithPublishedCategoryAndPreset(ucwords($request->for));
            }
            $final_complains = collect();
            $final_presets = collect();
            $presets = $accessor->complainPresets;
            foreach ($presets as $preset) {
                $final_presets->push(collect($preset)->only(['id', 'name', 'category_id']));
            }
            $categories = $accessor->complainCategories;
            foreach ($categories as $category) {
                $final = collect($category)->only(['id', 'name']);
                $final->put('presets', $final_presets->where('category_id', $category->id)->values()->all());
                if(!empty($final['presets'])) {
                    $final_complains->push($final);
                }
            }
            return api_response($request, null, 200, ['complains' => $final_complains, 'accessor_id' => $accessor->id]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /* private function getJobWiseCategory($job, $accessor)
    {
        if (!empty($job)) {
            $job_status = $this->getJobStatus($job);
        }
        return !empty($job) ? $accessor->complainCategories->filter(function ($cat) use ($job_status) {
            return $cat->order_status == $job_status;
        }) : $accessor->complainCategories;
    }

    private function getCategoryWisePresets($job, $accessor)
    {
        return !empty($job) ? $accessor->complainPresets->filter(function ($q) use ($job) {
            return $q->subCategories->filter(function ($cat) use ($job) {
                    return $job->category_id == $cat->id;
                })->count() > 0;
        }) : $accessor->complainPresets;
    } */

    private function getJobStatus($job)
    {
        if ($job->status === 'Cancelled' || $job->status === 'Closed'||$job->status==='Served') {
            return 'closed';
        } else {
            return 'open';
        }
    }

    public function showCustomerComplain($customer, $job, $complain, Request $request)
    {
        try {
            $customer = $request->customer;
            $complain = $this->getComplain($complain, $customer);

            if ($complain) {
                $comments = $this->formationComments($complain->comments);
                $complain['comments'] = $comments;
                $complain['code'] = $complain->code();
                return api_response($request, null, 200, ['complain' => $complain]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function showPartnerComplain($partner, $complain, Request $request)
    {
        try {
            $partner = $request->manager_resource;
            $complain = $this->getComplain($complain, $partner);

            if ($complain) {
                $comments = $this->formationComments($complain->comments);
                $complain['comments'] = $comments;
                $complain['code'] = $complain->code();
                return api_response($request, null, 200, ['complain' => $complain]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    protected function getComplain($complain, $accessor)
    {
        $complain = Complain::where('id', $complain)->select('id', 'status', 'complain', 'accessor_id', 'job_id', 'customer_id', 'created_at', 'complain_preset_id')
            ->with(['preset' => function ($q) {
                $q->select('id', 'name', 'category_id')->with(['complainCategory' => function ($q) {
                    $q->select('id', 'name');
                }]);
            }])->with(['comments' => function ($q) use ($accessor) {
                $q->select('id', 'comment', 'commentable_type', 'commentable_id', 'commentator_id', 'commentator_type', 'created_at')
                    ->whereHas('accessors', function ($q) use ($accessor) {
                        $q->where('model_name', get_class($accessor));
                    })->with(['commentator' => function ($q) {
                        $q->select('*');
                    }])->orderBy('id', 'asc');
            }])->first();
        return $complain;
    }

    private function formationComments($comments)
    {
        foreach ($comments as &$comment) {
            array_forget($comment, 'commentable_type');
            array_forget($comment, 'commentable_id');
            array_forget($comment, 'commentator_id');
            $comment['commentator_type'] = strtolower(str_replace('App\Models\\', "", $comment->commentator_type));
            $comment['prettified_date'] = $comment->created_at->format('jS F, Y g:i A');
            if (class_basename($comment->commentator) == 'User') {
                $comment['commentator_name'] = $comment->commentator->name;
                $comment['commentator_picture'] = $comment->commentator->profile_pic;
            } else {
                $comment['commentator_name'] = $comment->commentator->profile->name;
                $comment['commentator_picture'] = $comment->commentator->profile->pro_pic;
            }
            removeRelationsAndFields($comment);
        }
        return $comments;
    }

    public function storeForCustomer($customer, $job, Request $request)
    {
        try {
            $this->validate($request, [
                'accessor_id' => 'numeric',
                'complain_preset' => 'required|numeric',
                'complain' => 'sometimes|string',
            ]);
            $last_job = $request->job->partnerOrder->order->lastJob();
            $this->setModifier($request->customer);
            $data = $this->processCommonData($request, 'Partner');
            $data = array_merge($data, $this->processJobData($request, $last_job));
            $data = $this->withCreateModificationField($data);

            $complain = $this->complainRepo->create($data);
            (new ComplainNotification($complain))->notifyOnCreate();
            $response = $complain->preset->response;

            return api_response($request, $complain, 200, ['response' => $response]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeForPartner($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'accessor_id' => 'numeric',
                'complain_preset' => 'required|numeric',
                'complain' => 'sometimes|string',
            ]);
            if ($request->job) {
                $job = Job::find((int)$request->job);
                if (!$job || $job->partnerOrder->partner_id != (int)$partner) return api_response($request, null, 403, ['message' => "This is not your Job"]);
            }
            $this->setModifier($request->manager_resource);
            $data = $this->processCommonData($request, 'Customer');
            if ($request->job) $data = array_merge($data, $this->processJobData($request, $job));
            $data = $this->withCreateModificationField($data);

            $complain = $this->complainRepo->create($data);
            (new ComplainNotificationPartner($complain))->notifyQcAndCrmOnCreate();
            $response = $complain->preset->response;

            return api_response($request, $complain, 200, ['response' => $response]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    protected function processCommonData(Request $request, $for = 'Partner')
    {
        $preset_id = (int)$request->complain_preset;
        $preset = $this->complainPresetRepo->find($preset_id);
        $follow_up_time = Carbon::now()->addMinutes($preset->complainType->sla);
        $accessor = $preset->accessors->count() > 1 ? $preset->accessors->filter(function ($accessor) use ($for) {
            return $accessor->name == $for;
        })->first() : $preset->accessors->first();
        return [
            'complain' => $request->complain,
            'complain_preset_id' => $preset_id,
            'follow_up_time' => $follow_up_time,
            'accessor_id' => $accessor ? $accessor->id : $request->accessor_id
        ];
    }

    protected function processJobData(Request $request, Job $job)
    {
        return [
            'job_id' => $request->has('job_id') ? $request->job_id : $job->id,
            'customer_id' => isset($request->customer_id) ? $request->customer_id : $job->partnerOrder->order->customer_id,
            'partner_id' => empty($request->partner_id) ? $job->partnerOrder->partner_id : $request->partner_id
        ];
    }

    public function postCustomerComment($customer, $job, $complain, Request $request)
    {
        try {
            $customer = $request->customer;
            $response = $this->postComment($request, $complain, $customer);
            if ($response['code'] == 200) return api_response($request, $response['complain'], 200);
            else return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function postPartnerComment($partner, $complain, Request $request)
    {
        try {
            $partner = $request->manager_resource;
            $response = $this->postComment($request, $complain, $partner);
            if ($response['code'] == 200) return api_response($request, $response['complain'], 200);
            else return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    protected function postComment(Request $request, $complain, $accessor)
    {
        $this->setModifier($accessor);
        $comment = new Comment();
        $comment->comment = $request->comment;
        $comment->commentable_type = "Sheba\\Dal\\Complain\\Model";
        $comment->commentable_id = $complain;
        $comment->commentator_type = get_class($accessor);
        $comment->commentator_id = (int)$accessor->id;
        if ($comment->save()) {
            $accessor_id = Accessor::where('model_name', get_class($accessor))->first()->id;
            $comment->accessors()->attach($accessor_id);
            (new CommentNotification())->send($this->complainRepo->find($complain));
            return [
                'code' => 200,
                'complain' => $complain
            ];
        } else {
            return ['code' => 500];
        }
    }

    public function complainList(Request $request)
    {
        try {
            $accessor = null;
            if ($request->has('created_by')) {
                if (ucwords($request->created_by) == 'Partner') $accessor = "Partner";
                elseif (ucwords($request->created_by) == 'Customer') $accessor = "Customer";
            }

            $complains = $this->complainRepo->partnerComplainList($request->partner->id, $accessor, ($request->has('not_resolved') && $request->not_resolved));
            $complains = $complains->sortByDesc('id')->take(20);
            $formatted_complains = collect();

            foreach ($complains as $complain) {
                $order_code = 'N/A';
                $customer_name = 'N/A';
                $customer_profile_picture = null;
                $schedule_date_and_time = null;
                $job_category = 'N/A';
                $job_location = 'N/A';
                $resource_name = 'N/A';
                $partner_order_id = null;
                if ($complain->job) {
                    $order = $complain->job->partnerOrder->order;
                    $customer_profile = $order->customer->profile;
                    $order_code = $order->code();
                    $customer_name = $customer_profile->name;
                    $customer_profile_picture = $customer_profile->pro_pic;
                    $schedule_date_and_time = humanReadableShebaTime($complain->job->preferred_time) . ', ' . Carbon::parse($complain->job->schedule_date)->toFormattedDateString();
                    $job_category = $complain->job->category->name;
                    $job_location = $order->location->name;
                    $resource_name = $complain->job->resource ? $complain->job->resource->profile->name : 'N/A';
                    $partner_order_id = $complain->job->partnerOrder->id;
                }
                $formatted_complains->push([
                    'id' => $complain->id,
                    'complain_code' => $complain->code(),
                    'complain' => $complain->complain,
                    'order_code' => $order_code,
                    'order_id' => $partner_order_id,
                    'customer_name' => $customer_name,
                    'customer_profile_picture' => $customer_profile_picture,
                    'schedule_date_and_time' => $schedule_date_and_time,
                    'category' => $job_category,
                    'location' => $job_location,
                    'resource' => $resource_name,
                    'complain_category' => $complain->preset->complainCategory->name,
                    'status' => $complain->status == Statuses::OBSERVATION ? Statuses::OPEN : $complain->status,
                    'created_at' => $complain->created_at->format('jS F, Y')
                ]);
            }
            return api_response($request, $formatted_complains, 200, ['complains' => $formatted_complains]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateStatus($partner, $complain, Request $request, ComplainStatusChanger $status_changer)
    {
        try {
            $this->validate($request, [
                'status' => 'required|string',
                'resolved_category' => 'sometimes|string',
            ]);
            $this->setModifier($request->manager_resource);
            $complain = $this->complainRepo->find($complain);
            $status_changer->setComplain($complain)->setData($request->all());
            if ($error = $status_changer->hasError()) return api_response($request, $error, 400, ['message' => $error]);
            $status_changer->setModifierForModificationFiled($request->manager_resource)->change();
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function resolvedCategory(Request $request)
    {
        $resolved_category = constants('COMPLAIN_RESOLVE_CATEGORIES');
        return api_response($request, $resolved_category, 200, ['resolved_category' => $resolved_category]);
    }
}
