<?php namespace App\Models;

use Sheba\Dal\BaseModel;

class Attachment extends BaseModel
{
    protected $guarded = ['id'];

    public function attachable()
    {
        return $this->morphTo();
    }
}
