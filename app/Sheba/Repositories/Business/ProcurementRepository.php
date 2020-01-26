<?php namespace Sheba\Repositories\Business;

use App\Models\Procurement;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\BaseModel;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class ProcurementRepository extends BaseRepository implements ProcurementRepositoryInterface
{
    public function __construct(Procurement $procurement)
    {
        parent::__construct();
        $this->setModel($procurement);
    }

    public function ofBusiness($business_id)
    {
        return $this->model->where('owner_id', $business_id)->where('owner_type', "App\\Models\\Business");
    }

   public function update(Model $model, array $data)
   {
       return $model->lockForUpdate()->update($this->withUpdateModificationField($data));
   }
}