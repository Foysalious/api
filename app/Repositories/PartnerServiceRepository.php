<?php namespace App\Repositories;

use Sheba\Dal\PartnerService\PartnerService;
use App\Models\PartnerServiceSurcharge;
use Sheba\Dal\Service\Service;
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

    /**
     * @param $profile_infos
     * @return float
     */
    public function profileCompletedProgress($profile_infos)
    {
        $profile_data = $this->dataRatio($profile_infos, ['pro_pic', 'email', 'address', 'permanent_address']);
        $nid_data = $this->dataRatio($profile_infos, ['pro_pic', 'father_name', 'mother_name', 'email', 'address', 'permanent_address', 'dob', 'nid_no', 'nid_image_front', 'nid_image_back']);

        return ($profile_data * .5 + $nid_data * .5) * 100;
    }

    /**
     * @param $profile_infos
     * @param array $data_set
     * @return float|int
     */
    private function dataRatio($profile_infos, array $data_set)
    {
        $count = 0;
        foreach ($data_set as $data) {
            if (!empty($profile_infos->$data)) $count ++;
        }
        return $count/sizeof($data_set);
    }

    /**
     * @param $profile_infos
     * @return bool
     */
    public function isProfileCompleted($profile_infos): bool
    {
        return (int) $this->profileCompletedProgress($profile_infos) == 100;
    }
}