<?php namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Bid;
use App\Models\Business;
use App\Models\Partner;
use App\Models\Procurement;
use App\Sheba\Attachments\Attachments;
use App\Transformers\AttachmentTransformer;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use ModificationFields;

    public function storeAttachment($avatar, $attachable, Request $request, Attachments $attachments)
    {
        try {
            $this->validate($request, [
                'file' => 'required'
            ]);
            if ($request->segment(4) == 'bids') {
                $attachable = Bid::findOrFail((int)$attachable);
            } elseif ($request->segment(4) == 'procurements') {
                $attachable = Procurement::findOrFail((int)$attachable);
            }

            if ($request->segment(2) == 'businesses') {
                $avatar = Business::findOrFail((int)$avatar);
            } elseif ($request->segment(2) == 'partners') {
                $avatar = Partner::findOrFail((int)$avatar);
            }
            if ($attachments->hasError($request))
                return redirect()->back();


            $this->setModifier($avatar);
            $attachment = $attachments->setAttachableModel($attachable)
                ->setRequestData($request)
                ->setFile($request->file)
                ->store();
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

    public function getAttachments($avatar, $attachable, Request $request)
    {
        try {
            if ($request->segment(4) == 'bids') {
                $attachable = Bid::findOrFail((int)$attachable);
            } elseif ($request->segment(4) == 'procurements') {
                $attachable = Procurement::findOrFail((int)$attachable);
            }
            if (!$attachable) return api_response($request, null, 404);
            list($offset, $limit) = calculatePagination($request);
            $attaches = Attachment::where('attachable_type', get_class($attachable))->where('attachable_id', $attachable->id)
                ->select('id', 'title', 'file', 'file_type')->orderBy('id', 'DESC')->skip($offset)->limit($limit)->get();
            $attach_lists = [];
            foreach ($attaches as $attach) {
                array_push($attach_lists, (new AttachmentTransformer())->transform($attach));
            }
            if (count($attach_lists) > 0) return api_response($request, $attach_lists, 200, ['attach_lists' => $attach_lists]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
