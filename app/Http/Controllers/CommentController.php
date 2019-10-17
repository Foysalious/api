<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\FuelLog;
use App\Repositories\CommentRepository;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class CommentController extends Controller
{
    use ModificationFields;

    /*public function getBusinessComments($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $fuel_log = FuelLog::find((int)$log);
            if (!$fuel_log) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $comments = Comment::where('commentable_type', get_class($fuel_log))->where('commentable_id', $fuel_log->id)->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
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
    }*/

    public function storeComments(Request $request)
    {
        try {
            $this->validate($request, [
                'comment' => 'required'
            ]);
            #Under
            $commentable_model = "App\\Models\\" . ucfirst(camel_case($request->commentable_type));
            $commentable_type = $commentable_model::find((int)$request->commentable_id);

            #Who
            $commentator_model = "App\\Models\\" . ucfirst(camel_case($request->commentator_type));
            $commentator_type = $commentator_model::find((int)$request->commentator_id);
            dd($commentator_type->members);
            $this->setModifier($commentator_type);

            $comment = (new CommentRepository(ucfirst(camel_case($request->commentable_type)), $commentable_type->id, $commentator_type))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}