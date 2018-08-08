<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Dal\Accessor\Model as Accessor;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\Accessor\Contract as AccessorRepo;
use Sheba\Dal\ComplainPreset\Contract as ComplainPresetRepo;

use Illuminate\Http\Request;
use Sheba\Dal\Complain\EloquentImplementation as ComplainRepo;

class ComplainController extends Controller
{
    private $accessorRepo;
    private $complainPresetRepo;
    private $complainRepo;

    public function __construct(ComplainRepo $complain, AccessorRepo $accessorRepo, ComplainPresetRepo $complain_preset_repo)
    {
        $this->accessorRepo = $accessorRepo;
        $this->complainPresetRepo = $complain_preset_repo;
        $this->complainRepo = $complain;
    }

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'required|in:customer,partner'
            ]);
            $accessor = $this->accessorRepo->findByNameWithPublishedCategoryAndPreset(ucwords($request->for));
            $final_complains = collect();
            $final_presets = collect();
            foreach ($accessor->complainPresets as $preset) {
                $final_presets->push(collect($preset)->only(['id', 'name', 'category_id']));
            }
            foreach ($accessor->complainCategories as $category) {
                $final = collect($category)->only(['id', 'name']);
                $final->put('presets', $final_presets->where('category_id', $category->id)->values()->all());
                $final_complains->push($final);
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
        $complain = Complain::whereHas('accessor', function ($query) use ($accessor) {
            $query->where('accessors.model_name', get_class($accessor));
        })->where('id', $complain)->select('id', 'status', 'complain', 'accessor_id', 'job_id', 'customer_id', 'created_at', 'complain_preset_id')
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
                    }])->orderBy('id', 'desc');
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
                'accessor_id' => 'required|numeric',
                'complain_preset' => 'required|numeric',
                'complain' => 'sometimes|string',
            ]);
            $data = $this->processCommonData($request);
            $data = array_merge($data, $this->processJobData($request, $request->job));
            $complain = $this->complainRepo->create($data);
            $response = $this->autoResponse($complain);

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
                'accessor_id' => 'required|numeric',
                'complain_preset' => 'required|numeric',
                'complain' => 'sometimes|string',
            ]);
            if ($request->job) {
                $job = Job::find((int) $request->job);
                if (!$job || $job->partnerOrder->partner_id != (int) $partner) return api_response($request, null, 403, ['message' => "This is not your Job"]);
            }
            $data = $this->processCommonData($request);
            if ($request->job) $data = array_merge($data, $this->processJobData($request, $job));
            $complain = $this->complainRepo->create($data);
            $response = $this->autoResponse($complain);

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

    protected function autoResponse(Complain $complain)
    {
        if ($response = $complain->preset->response) {
            $user = User::where('email', 'bot@sheba.xyz')->first();
            Comment::create([
                'comment' => $response,
                'commentable_type' => 'Sheba\Dal\Complain\Model',
                'commentable_id' => $complain->id,
                'commentator_type' => 'App\Models\User',
                'commentator_id' => $user->id,
                'created_by' => $user->id,
                'created_by_name' => $user->name,
                'updated_by' => $user->id,
                'updated_by_name' => $user->name
            ])->accessors()->sync(['accessor_id' => $complain->accessor_id]);

            return $response;
        }
    }

    protected function processCommonData(Request $request)
    {
        $preset_id = (int)$request->complain_preset;
        $preset = $this->complainPresetRepo->find($preset_id);
        $follow_up_time = Carbon::now()->addMinutes($preset->complainType->sla);

        return [
            'complain' => $request->complain,
            'complain_preset_id' => $preset_id,
            'follow_up_time' => $follow_up_time,
            'accessor_id' => $request->accessor_id
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
        $comment = new Comment();
        $comment->comment = $request->comment;
        $comment->commentable_type = "Sheba\\Dal\\Complain\\Model";
        $comment->commentable_id = $complain;
        $comment->commentator_type = get_class($accessor);
        $comment->commentator_id = (int)$accessor->id;
        if ($comment->save()) {
            $accessor_id = Accessor::where('model_name', get_class($accessor))->first()->id;
            $comment->accessors()->attach($accessor_id);
            return [
                'code' => 200,
                'complain' => $complain
            ];
        } else {
            return ['code' => 500];
        }
    }

}