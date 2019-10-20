<?php namespace App\Http\Controllers;

use App\Sheba\Attachments\Attachments;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use ModificationFields;

    public function storeAttachment(Request $request, Attachments $attachments)
    {
        try {
            $this->validate($request, [
                'file' => 'required'
            ]);
            if ($attachments->hasError($request))
                return redirect()->back();
            $attachments = $attachments->setAttachableType($request->attachable_type)->setAttachableId($request->attachable_id)->setFile($request->file);
            $this->setModifier($attachments->getAttachableModel());
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