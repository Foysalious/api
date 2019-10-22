<?php namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\Business;
use App\Models\Partner;
use App\Sheba\Attachments\Attachments;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use ModificationFields;

    public function storeAttachment($avatar, $bid, Request $request, Attachments $attachments)
    {
        try {
            $this->validate($request, [
                'file' => 'required'
            ]);
            $bid = Bid::findOrFail((int)$bid);

            if ($request->segment(2) == 'businesses') {
                $avatar = Business::findOrFail((int)$avatar);
            } elseif ($request->segment(2) == 'partners') {
                $avatar = Partner::findOrFail((int)$avatar);
            }
            if ($attachments->hasError($request))
                return redirect()->back();

            $attachments = $attachments->setAttachableModel($bid)->setRequestData($request)->setFile($request->file)->formatData();
            $this->setModifier($avatar);
            $attachment = $attachments->store();
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

}