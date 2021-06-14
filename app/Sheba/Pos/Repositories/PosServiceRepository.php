<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PartnerPosService;
use Carbon\Carbon;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class PosServiceRepository extends BaseRepository implements PosServiceRepositoryInterface
{
    public function __construct(PartnerPosService $partnerPosService)
    {
        parent::__construct();
        $this->setModel($partnerPosService);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return PartnerPosService::find($id);
    }

    public function delete($service)
    {
        return $service->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findWithTrashed($id)
    {
        return PartnerPosService::withTrashed()->find($id);
    }

    public function copy(PartnerPosService $partnerPosService)
    {
        $data = $partnerPosService->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['created_by_name'], $data['created_by_type'], $data['created_by'], $data['updated_by'], $data['updated_by_name'], $data['deleted_at']);
        $data['name'] = "copy of " . $data['name'];
        $service      = $this->save($data);
        $discount     = $partnerPosService->discount();
        if (!empty($discount)) {
            $service->discounts()->create(['amount' => $discount->amount, 'start_date' => Carbon::today(), 'end_date' => $discount->end_date]);
        }
        return $service;
    }

    /**
     * @param $data
     * @return PartnerPosService
     */
    public function save($data)
    {
        return PartnerPosService::create($this->withCreateModificationField($data));
    }

    public function defaultInstance($service, $partner)
    {
        $new_service                  = new PartnerPosService();
        $new_service->warranty        = isset($service['warrany']) ? $service['warranty'] : 0;
        $new_service->warranty_unit   = isset($service['warranty_unit']) ? $service['warranty_unit'] : "day";
        $new_service->vat_percentage  = isset($service['vat_percentage']) ? (double)$service['vat_percentage'] : $this->getDefaultVat($partner);
        $new_service->price           = isset($service['updated_price']) && $service['updated_price']? $service['updated_price'] : 0.0;
        $new_service->wholesale_price = $new_service->price;
        $new_service->name            = isset($service['name']) ? $service['name'] : "Custom Item";
        return $new_service;
    }

    public function getDefaultVat($partner)
    {
        $pos_setting = $partner->posSetting;
        return $pos_setting ? $pos_setting->vat_percentage : 0.0;
    }
}
