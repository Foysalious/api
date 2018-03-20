<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Sheba\Dal\Complain\Model as Complain;
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
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function show($customer, $job, $complain, Request $request)
    {
        try {
            $customer = $request->customer;
            $complain = Complain::whereHas('accessor', function ($query) use ($customer) {
                $query->where('accessors.model_name', get_class($customer));
            })->where('id', $complain)->select('id', 'status', 'complain', 'accessor_id', 'job_id', 'customer_id', 'created_at', 'complain_preset_id')
                ->with(['preset' => function ($q) {
                    $q->select('id', 'name', 'category_id')->with(['complainCategory' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }])->with(['comments' => function ($q) {
                    $q->select('id', 'comment', 'commentable_type', 'commentable_id', 'commentator_id', 'commentator_type', 'created_at')
                        ->with(['commentator' => function ($q) {
                            $q->select('id');
                        }])->orderBy('id', 'desc');
                }])->first();
            if ($complain) {
                $comments = $this->formationComments($complain->comments);
                removeRelationsAndFields($complain);
                $complain['comments'] = $comments;
                return api_response($request, null, 200, ['complain' => $complain]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function formationComments($comments)
    {
        foreach ($comments as &$comment) {
            array_forget($comment, 'commentable_type');
            array_forget($comment, 'commentable_id');
            array_forget($comment, 'commentator_id');
            $comment['commentator_type'] = strtolower(str_replace('App\Models\\', "", $comment->commentator_type));
        }
        return $comments;
    }

    public function store($customer, $job, Request $request)
    {
        try {
            $this->validate($request, [
                'accessor_id' => 'required|numeric',
                'complain_preset' => 'required|numeric',
                'complain' => 'sometimes|string',
            ]);
            $job = $request->job;
            $data = $this->processData($request, $job);
            $complain = $this->complainRepo->create($data);
            return api_response($request, $complain, 200, ['response' => $complain->preset->response]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    protected function processData(Request $request, Job $job)
    {
        $preset_id = (int)$request->complain_preset;
        $preset = $this->complainPresetRepo->find($preset_id);
        $follow_up_time = Carbon::now()->addMinutes($preset->complainType->sla);

        return [
            'complain' => $request->complain,
            'complain_preset_id' => $preset_id,
            'follow_up_time' => $follow_up_time,
            'accessor_id' => $request->accessor_id,
            'job_id' => empty($request->job_id) ? null : $request->job_id,
            'customer_id' => isset($request->customer_id) ? $request->customer_id : $job->partnerOrder->order->customer_id,
            'partner_id' => empty($request->partner_id) ? $request->partner_id : $job->partnerOrder->partner_id
        ];
    }

    public function postComment($customer, $job, $complain, Request $request)
    {
        try {
            $customer = $request->customer;
            $comment = new Comment();
            $comment->comment = $request->comment;
            $comment->commentable_type = "Sheba\\Dal\\Complain\\Model";
            $comment->commentable_id = $complain;
            $comment->commentator_type = get_class($customer);
            $comment->commentator_id = (int)$customer->id;
            if ($comment->save()) {
                return api_response($request, $complain, 200);
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

}