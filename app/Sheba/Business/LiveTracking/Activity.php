<?php namespace App\Sheba\Business\LiveTracking;

use Sheba\Helpers\ConstGetter;

class Activity
{
    use ConstGetter;

    const All = 'all';
    const ONE_HOUR = '60';
    const TWO_HOURS = '120';
    const FOUR_HOURS = '240';
    const SIX_HOURS = '360';
    const EIGHT_HOURS = '480';

    public function getActivity()
    {
        return [
            0 => [
                'key' => self::All,
                'value' => 'All',
            ],
            1 => [
                'key' => self::ONE_HOUR,
                'value' => 'No activity since 1 hour',
            ],
            2 => [
                'key' => self::TWO_HOURS,
                'value' => 'No activity since 2 hours',
            ],
            3 => [
                'key' => self::FOUR_HOURS,
                'value' => 'No activity since 4 hours',
            ],
            4 => [
                'key' => self::SIX_HOURS,
                'value' => 'No activity since 6 hours',
            ],
            5 => [
                'key' => self::EIGHT_HOURS,
                'value' => 'No activity since 8 hours',
            ],
        ];
    }
}