<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardAction extends Model
{
    protected $guarded = ['id'];

    public function reward()
    {
        return $this->morph('App\Models\Reward', 'detail_type');
    }
}
