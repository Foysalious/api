<?php namespace Sheba\Repositories;


use App\Models\Profile;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    public function __construct(Profile $profile)
    {
        parent::__construct();
        $this->setModel($profile);
    }

    public function findByMobile($mobile)
    {
        return $this->model->where('mobile', 'like', '%' . formatMobile($mobile) . '%');
    }
}