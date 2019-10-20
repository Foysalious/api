<?php namespace App\Http\Controllers;

use App\Sheba\Comment\Comments;
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

    public function storeComments(Request $request, Comments $comments)
    {
        try {
            $this->validate($request, [
                'comment' => 'required'
            ]);
            $comments = $comments->setCommentableType($request->commentable_type)->setCommentableId($request->commentable_id)
                ->setCommentatorType($request->commentator_type)->setCommentatorId($request->commentator_id)->setComment($request->comment);
            $this->setModifier($comments->getCommentatorModel());
            $comment = $comments->store();
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
}