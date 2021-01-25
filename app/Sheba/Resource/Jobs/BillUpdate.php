<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use Sheba\Dal\JobService\JobService;
use Sheba\Services\ServicePriceCalculation;

class BillUpdate
{

    private $servicePriceCalculation;
    private $billInfo;
    private $vat_percentage;

    public function __construct(ServicePriceCalculation $servicePriceCalculation, BillInfo $billInfo)
    {
        $this->servicePriceCalculation = $servicePriceCalculation;
        $this->billInfo = $billInfo;
        $this->vat_percentage = (double) config('sheba.category_vat_in_percentage');
    }

    public function getUpdatedBillForServiceAdd(Job $job)
    {
        $location = $job->partnerOrder->order->location->geo_informations;
        $location = json_decode($location);
        $setServiceLocation = $this->servicePriceCalculation->setLocation($location->lat, $location->lng)->setServices(\request('services'));
        $price = $setServiceLocation->getCalculatedPrice();
        $new_service = $setServiceLocation->createServiceList()->toArray();
        $bill = $this->billInfo->getBill($job);
        $bill['total'] += $price['total_discounted_price'];
        $bill['due'] = $bill['due'] - $bill['vat'];
        $bill['due'] += $price['total_discounted_price'];
        $bill['due'] += ceil($bill['due']*$this->vat_percentage/100);
        $bill['vat'] = ceil($bill['total']*$this->vat_percentage/100);
        $bill['total_service_price'] += $price['total_original_price'];
        $bill['discount'] += $price['total_discount'];
        $bill['services'] = array_merge($bill['services'], $this->formatService($new_service));
        $bill['service_list'] = $this->addNewServiceToServiceList($bill['service_list'], $new_service);
        return $bill;
    }

    public function getUpdatedBillForMaterialAdd(Job $job)
    {
        $bill = $this->billInfo->getBill($job);
        $new_materials = json_decode(\request('materials'));
        $total_material_price = $this->calculateTotalMaterialPrice($new_materials);
        $bill['total'] += (double) $total_material_price;
        $bill['due'] = $bill['due'] - $bill['vat'];
        $bill['due'] += (double) $total_material_price;
        $bill['due'] += ceil($bill['due']*$this->vat_percentage/100);
        $bill['vat'] = ceil($bill['total']*$this->vat_percentage/100);
        $bill['total_material_price'] += (double) $total_material_price;
        $materials = $bill['materials']->toArray();
        $bill['materials'] = array_merge($materials, $this->formatMaterial(collect($new_materials)->toArray()));
        return $bill;
    }

    public function getUpdatedBillForQuantityUpdate(Job $job)
    {
        $quantity = json_decode(\request('quantity'),1);
        $bill = $this->billInfo->getBill($job);
        $services = $this->updateServicesQuantity($bill['services'], $quantity);
        $updated_service_price = $this->calculateUpdatedTotalServicePrice($services);
        $increased_amount = $this->calculateIncreasedAmount($updated_service_price, $job->servicePrice);
        $bill['total'] = $bill['total'] + $increased_amount;
        $bill['due'] = ceil($bill['due'] + $increased_amount + $increased_amount*$this->vat_percentage/100);
        $bill['vat'] = ceil($bill['total']*$this->vat_percentage/100);
        $bill['total_service_price'] = $updated_service_price;
        $bill['services'] = $services;
        $bill['service_list'] = $this->updateServiceOfServiceList($bill['service_list'], $quantity);
        return $bill;
    }

    private function formatService($services)
    {
        $services = array_map(function($service) {
            return array(
                'id' => null,
                'name' => $service['service_name'],
                'price' => $service['original_price'],
                'unit' => $service['unit'],
                'quantity' => $service['quantity']
            );
        }, $services);
        return $services;
    }

    private function formatMaterial($materials)
    {
        $materials = array_map(function($material) {
            return array(
                'id' => null,
                'material_name' => $material->name,
                'material_price' => (double)$material->price,
                'job_id' => null
            );
        }, $materials);
        return $materials;
    }

    private function calculateTotalMaterialPrice($new_materials)
    {
        $total_material_price = 0.00;
        foreach ($new_materials as $material) {
            $total_material_price += $material->price;
        }
        return $total_material_price;
    }

    private function updateServicesQuantity($services, $quantity)
    {
        $updated_services = $services;
        foreach ($services as $key => $service) {
            foreach ($quantity as $qty) {
                if ($service['id'] == $qty['job_service_id']) {
                    $updated_services[$key]['id'] = null;
                    $updated_services[$key]['quantity'] = $qty['quantity'];
                    $updated_services[$key]['price'] = $service['price'] / $service['quantity'] * $qty['quantity'];
                }
            }
        }
        return $updated_services;
    }

    private function calculateUpdatedTotalServicePrice($services)
    {
        $total_service_price = 0.00;
        foreach ($services as $service) {
            $total_service_price += $service['price'];
        }
        return $total_service_price;
    }

    private function calculateIncreasedAmount($updated, $previous)
    {
        return (double) $updated - $previous;
    }
    private function addNewServiceToServiceList($services , $services_to_add)
    {
        foreach ($services_to_add as &$service_to_add) {
            if ($service_to_add['variable_type'] == 'Fixed') {
                $services->push($this->formatFixedService($service_to_add));
            } else {
                $matched = 0;
                foreach ($services as &$service) {
                    if ($service['id'] == $service_to_add['service_id']) {
                        $service['service_group']->push($this->formatGroupedService($service_to_add));
                       $matched++;
                        //TODO: Need to Update Quantity
                    }
                }
                if ($matched==0) $services->push($this->formatOptionService($service_to_add));
            }
        }
        return $services;
    }
    private function updateServiceOfServiceList($services , $services_to_update)
    {
        $services = $services->toArray();
        foreach ($services_to_update as $service_to_update) {
            $job_service = JobService::find($service_to_update['job_service_id']);
            foreach ($services as &$service) {
                if ($service['id'] == $job_service->service_id) {
                    if ($job_service->variable_type == 'Fixed') {
                        $previous_qty = $service['quantity'];
                        $service['id'] = null;
                        $service['quantity'] = $service_to_update['quantity'];
                        $service['price'] = $service['price'] / $previous_qty * $service_to_update['quantity'];
                    } else {
                        $service['service_group'] = $this->updateGroupedServices($service['service_group'], $service_to_update);
                    }
                }
            }
        }
        return $services;
    }
    private function formatGroupedService($service)
    {
        return array(
            'job_service_id' => null,
            'variables' => json_decode($service['variables']),
            'unit' => $service['unit'],
            'quantity' => $service['quantity'],
            'price' => $service['unit_price'] * $service['quantity']
        );
    }
    private function formatFixedService($service)
    {
        return array(
            'id' => null,
            'name' => $service['service_name'],
            'service_group' => [],
            'unit' => $service['unit'],
            'quantity' => $service['quantity'],
            'price' => $service['unit_price'] * $service['quantity']
        );
    }
    private function updateGroupedServices($services_group, $service)
    {
        $services_group = is_array($services_group) ? $services_group : $services_group->toArray();
        $services_group = array_map(function($service_group) use ($service) {
            if ($service_group['job_service_id'] == $service['job_service_id']) {
                return array(
                    'job_service_id' => null,
                    'variables' => $service_group['variables'],
                    'price' => $service_group['price'] / $service_group['quantity'] * $service['quantity'],
                    'unit' => $service_group['unit'],
                    'quantity' => $service['quantity']
                );
            } else {
                return array(
                    'job_service_id' => $service_group['job_service_id'],
                    'variables' => $service_group['variables'],
                    'price' => $service_group['price'],
                    'unit' => $service_group['unit'],
                    'quantity' => $service_group['quantity']
                );
            }
        }, $services_group);
        return $services_group;
    }

    private function formatOptionService($service)
    {
        return array(
            'id' => null,
            'name' => $service['service_name'],
            'service_group' => [
                [
                    'job_service_id' => null,
                    'variables' => json_decode($service['variables']),
                    'unit' => $service['unit'],
                    'quantity' => $service['quantity'],
                    'price' => $service['unit_price'] * $service['quantity']
                ]
            ],
            'unit' => $service['unit'],
            'quantity' => $service['quantity'],
            'price' => $service['unit_price'] * $service['quantity']
        );
    }
}
