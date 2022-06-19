<?php namespace App\Sheba\Business\LiveTracking;


class DateDropDown
{
    const START = 1;
    const END = 6;

    /**
     * @param $last_tracked_location
     * @return array
     */
    public function getDateDropDown($last_tracked_location)
    {
        $last_tracked_date = $last_tracked_location->date;
        $date_dropdown = [];

        $date_dropdown[] = [
            'key' => $last_tracked_date->toDateString(),
            'value' => $this->isForWeb() ?
                $last_tracked_date->format('d/m/Y') :
                $last_tracked_date->format('d F, Y')
        ];

        for ($day = self::START; $day <= self::END; $day++) {
            $last_date = $last_tracked_date->subDay();
            $date_dropdown[] = [
                'key' => $last_date->toDateString(),
                'value' => $this->isForWeb() ?
                    $last_date->format('d/m/Y') :
                    $last_date->format('d F, Y')
            ];
        }

        return [$date_dropdown[0], $date_dropdown];
    }

    /**
     * @return bool
     */
    private function isForWeb()
    {
        if (request()->has('for') && request()->for == "web") return true;
        return false;
    }
}