<?php namespace App\Http\Controllers\B2b;

use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Sheba\Business\ACL\AccessControl;
use Illuminate\Validation\ValidationException;
use Sheba\Attachments\FilesAttachment;
use App\Http\Controllers\Controller;
use App\Models\InspectionItemIssue;
use Sheba\Business\Issue\Creator;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Attachment;
use Carbon\Carbon;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use Sheba\Repositories\Interfaces\IssueRepositoryInterface;

class IssueController extends Controller
{
    use ModificationFields;
    use FilesAttachment;

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            list($offset, $limit) = calculatePagination($request);
            $inspection_item_issues = InspectionItemIssue::whereHas('inspectionItem', function ($q) use ($business) {
                $q->whereHas('inspection', function ($q) use ($business) {
                    $q->with('vehicle.basicInformation')->where('business_id', $business->id);
                });
            })->orderBy('id', 'DESC')->skip($offset)->limit($limit);


            if ($request->filled('status')) {
                $inspection_item_issues = $inspection_item_issues->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $inspection_item_issues = $inspection_item_issues->whereHas('inspectionItem', function ($q) use ($request) {
                    $q->whereHas('inspection', function ($q) use ($request) {
                        $q->whereHas('vehicle', function ($query) use ($request) {
                            $query->whereHas('basicInformations', function ($query) use ($request) {
                                $query->where('type', $request->type);
                            });
                        });
                    });
                });
            }

            $issue_lists = [];
            foreach ($inspection_item_issues->get() as $issue) {
                $inspection = $issue->inspectionItem->inspection;
                $vehicle = $inspection->vehicle;
                $basic_information = $vehicle->basicInformations;
                $issue = [
                    'id' => $issue->id,
                    'title' => $issue->inspectionItem->title,
                    'short_description' => $issue->inspectionItem->short_description,
                    'long_description' => $issue->inspectionItem->long_description,
                    'status' => $issue->status,
                    'comment' => $issue->comment,
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_model' => $basic_information->model_name,
                        'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                        'status' => $vehicle->status,
                        'vehicle_type' => $basic_information->type,
                        'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    ],
                ];
                array_push($issue_lists, $issue);
            }
            if (count($issue_lists) > 0) return api_response($request, $issue_lists, 200, ['issue_lists' => $issue_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($business, $issue, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $issue = InspectionItemIssue::find((int)$issue);
            if (!$issue) return api_response($request, null, 404);
            $issue_lists = [];
            $inspection = $issue->inspectionItem->inspection;
            $vehicle = $inspection->vehicle;
            $basic_information = $vehicle->basicInformations;
            $driver = $vehicle->driver;
            $issue = [
                'title' => $issue->inspectionItem->title,
                'short_description' => $issue->inspectionItem->short_description,
                'long_description' => $issue->inspectionItem->long_description,
                'status' => $issue->status,
                'comment' => $issue->comment,
                'inspector' => $inspection->member->profile->name,
                'inspection_form' => $inspection->formTemplate ? $inspection->formTemplate->title : null,
                'submitted' => $inspection->submitted_date ? Carbon::parse($inspection->submitted_date)->format('j M') : null,

                'vehicle' => [
                    'id' => $vehicle->id,
                    'vehicle_model' => $basic_information->model_name,
                    'model_year' => Carbon::parse($basic_information->model_year)->format('Y'),
                    'status' => $vehicle->status,
                    'vehicle_type' => $basic_information->type,
                    'assigned_to' => $vehicle->businessDepartment ? $vehicle->businessDepartment->name : null,
                    'current_driver' => $driver ? $vehicle->driver->profile->name : 'N/S',
                ],
            ];

            if (count($issue) > 0) return api_response($request, $issue, 200, ['issue' => $issue]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAttachments($business, $issue, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $issue = InspectionItemIssue::find((int)$issue);
            if (!$issue) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $attaches = Attachment::where('attachable_type', get_class($issue))->where('attachable_id', $issue->id)
                ->select('id', 'title', 'file', 'file_type')->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
            $attach_lists = [];
            foreach ($attaches as $attach) {
                array_push($attach_lists, [
                    'id' => $attach->id,
                    'title' => $attach->title,
                    'file' => $attach->file,
                    'file_type' => $attach->file_type,
                ]);
            }

            if (count($attach_lists) > 0) return api_response($request, $attach_lists, 200, ['attach_lists' => $attach_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeAttachment($business, $issue, Request $request)
    {
        try {
            $this->validate($request, [
                'file' => 'required'
            ]);
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $issue = InspectionItemIssue::find((int)$issue);
            if (!$request->hasFile('file'))
                return redirect()->back();
            $data = $this->storeAttachmentToCDN($request->file('file'));
            $attachment = $issue->attachments()->save(new Attachment($this->withBothModificationFields($data)));
            return api_response($request, $attachment, 200, ['attachment' => $attachment->file]);
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

    public function getComments($business, $issue, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $issue = InspectionItemIssue::find((int)$issue);
            if (!$issue) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $comments = Comment::where('commentable_type', get_class($issue))->where('commentable_id', $issue->id)->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
            $comment_lists = [];
            foreach ($comments as $comment) {
                array_push($comment_lists, [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'name' => $comment->commentator->profile->name,
                        'image' => $comment->commentator->profile->pro_pic
                    ],
                    'created_at' => $comment->created_at->toDateTimeString()
                ]);
            }
            if (count($comment_lists) > 0) return api_response($request, $comment_lists, 200, ['comment_lists' => $comment_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeComment($business, $issue, Request $request)
    {
        try {
            $this->validate($request, [
                'comment' => 'required'
            ]);
            $business = $request->business;
            $member = $request->manager_member;
            $issue = InspectionItemIssue::find((int)$issue);
            $comment = (new CommentRepository('InspectionItemIssue', $issue->id, $member))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
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

    public function store($business, Request $request, AccessControl $access_control, InspectionItemRepositoryInterface $inspection_item_repository, Creator $creator)
    {
        try {
            $this->validate($request, [
                'inspection_item_id' => 'required|numeric'
            ]);
            $this->setModifier($request->manager_member);
            $inspection_item = $inspection_item_repository->find($request->inspection_item_id);
            if (!$inspection_item) return api_response($request, null, 404);
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('inspection_item.rw') || $inspection_item->inspection->business_id !== (int)$business) return api_response($request, null, 403);
            if ($inspection_item->issue) return api_response($request, null, 403, ['message' => "Issue is already created."]);
            $issue = $creator->setInspectionItem($inspection_item)->create();
            return api_response($request, $issue, 200, ['issue' => $issue->id]);
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

    public function close($business, $issue, Request $request, AccessControl $access_control, IssueRepositoryInterface $issue_repository)
    {
        try {
            $this->validate($request, [
                'note' => 'required|string'
            ]);
            if (!$access_control->setBusinessMember($request->business_member)->hasAccess('inspection_issue.rw')) return api_response($request, null, 403);
            $this->setModifier($request->manager_member);
            $issue = $issue_repository->find($issue);
            $issue_repository->update($issue, ['status' => 'closed', 'comment' => $request->note]);
            return api_response($request, $issue, 200, ['issue' => $issue->id]);
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
}