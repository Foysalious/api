<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use Sheba\Services\ServicePriceCalculation;

class BillUpdate
{

    private $servicePriceCalculation;
    private $billInfo;

    public function __construct(ServicePriceCalculation $servicePriceCalculation, BillInfo $billInfo)
    {
        $this->servicePriceCalculation = $servicePriceCalculation;
        $this->billInfo = $billInfo;
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
        $bill['due'] += $price['total_discounted_price'];
        $bill['total_service_price'] += $price['total_original_price'];
        $bill['discount'] += $price['total_discount'];
        $bill['services'] = array_merge($bill['services'], $this->formatService($new_service));
        return $bill;
    }

    public function getUpdatedBillForMaterialAdd(Job $job)
    {
        $bill = $this->billInfo->getBill($job);
        $new_materials = json_decode(\request('materials'));
        $total_material_price = $this->calculateTotalMaterialPrice($new_materials);
        $bill['total'] += (double) $total_material_price;
        $bill['due'] += (double) $total_material_price;
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
        $bill['due'] = $bill['due'] + $increased_amount;
        $bill['total_service_price'] = $updated_service_price;
        $bill['services'] = $services;
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
}