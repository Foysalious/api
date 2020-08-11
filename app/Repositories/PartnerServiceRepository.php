<?php namespace App\Repositories;

use Sheba\Dal\PartnerService\PartnerService;
use App\Models\PartnerServiceSurcharge;
use App\Models\Service;
use Carbon\Carbon;

class PartnerServiceRepository
{
    public function getVariableOptionPriceOfService(Service $service, PartnerService $partner_service, Array $option)
    {
        if ($service->variable_type == 'Options') {
            $variables = [];
            $options = implode(',', $option);
            $unit_price = (double)(json_decode($partner_service->prices))->$options;
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
            $unit_price = (double)$partner_service->prices;
        }
        return array($unit_price, $option, $variables);
    }

    public function getPriceOfOptionsService($prices, $selected_option)
    {
        return $this->getPriceOfThisOption($prices, implode(',', $selected_option));
    }

    public function getMinimumPriceOfOptionsService($prices, $selected_option)
    {
        return $this->getPriceOfThisOption($prices, implode(',', $selected_option));
    }

    private function getPriceOfThisOption($prices, $option)
    {
        $prices = json_decode($prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return (double)$price;
            }
        }
        return null;
    }

    public function hasThisOption($prices, $option)
    {
        $prices = json_decode($prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return true;
            }
        }
        return false;
    }

    public function getVariableOptionOfService(Service $service, PartnerService $partner_service, Array $option)
    {
        if ($service->variable_type == 'Options') {
            $variables = [];
            $options = implode(',', $option);
            $unit_price = (double)(json_decode($partner_service->prices))->$options;
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
            $unit_price = (double)$partner_service->prices;
        }
        return array($unit_price, $option, $variables);
    }

    public function getSurchargePriceOfService($partner_service, Carbon $schedule_date_time)
    {
        $surcharge = PartnerServiceSurcharge::where('partner_service_id', $partner_service->id)->runningAt($schedule_date_time)->first();
        return $surcharge ? $surcharge->amount : 0;
    }
}