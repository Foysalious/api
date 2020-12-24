<?php namespace App\Transformers;

use App\Models\Attachment;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract
{
    /**
     * @param Attachment $attachment
     * @return array
     */
    public function transform(Attachment $attachment)
    {
        return [
            'id' => $attachment->id,
            'title' => $attachment->title,
            'file' => $attachment->file,
            'file_type' => $attachment->file_type,
        ];
    }
}
