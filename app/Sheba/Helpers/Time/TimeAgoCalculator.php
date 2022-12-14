<?php namespace Sheba\Helpers\Time;

class TimeAgoCalculator
{
    const SECONDS_IN_MINUTE = 60;
    const SECONDS_IN_HOUR = 3600;
    const SECONDS_IN_DAY = 86400;
    const SECONDS_IN_WEEK = 604800;
    const SECONDS_IN_MONTH = 2600640;
    const SECONDS_IN_YEAR = 31207680;

    private $differenceInSecond;

    public function __construct($time_ago)
    {
        $this->differenceInSecond = $this->getDifferenceInSeconds($time_ago);
    }

    public function get()
    {
        if ($this->isDifferenceInSeconds()) return $this->getInSeconds();
        if ($this->isDifferenceInMinutes()) return $this->getInMinutes();
        if ($this->isDifferenceInHours()) return $this->getInHours();
        if ($this->isDifferenceInDays()) return $this->getInDays();
        if ($this->isDifferenceInWeeks()) return $this->getInWeeks();
        if ($this->isDifferenceInMonths()) return $this->getInMonths();
        return $this->getInYears();
    }

    public function isDifferenceInSeconds()
    {
        return $this->differenceInSecond < self::SECONDS_IN_MINUTE;
    }

    public function isDifferenceInMinutes()
    {
        return self::SECONDS_IN_MINUTE <= $this->differenceInSecond && $this->differenceInSecond < self::SECONDS_IN_HOUR;
    }

    public function isDifferenceInHours()
    {
        return self::SECONDS_IN_HOUR <= $this->differenceInSecond && $this->differenceInSecond < self::SECONDS_IN_DAY;
    }

    public function isDifferenceInDays()
    {
        return self::SECONDS_IN_DAY <= $this->differenceInSecond && $this->differenceInSecond < self::SECONDS_IN_WEEK;
    }

    public function isDifferenceInWeeks()
    {
        return self::SECONDS_IN_WEEK <= $this->differenceInSecond && $this->differenceInSecond < self::SECONDS_IN_MONTH;
    }

    public function isDifferenceInMonths()
    {
        return self::SECONDS_IN_MONTH <= $this->differenceInSecond && $this->differenceInSecond < self::SECONDS_IN_YEAR;
    }

    public function isDifferenceInYears()
    {
        return self::SECONDS_IN_YEAR <= $this->differenceInSecond;
    }

    public function getInSeconds()
    {
        $diff = $this->differenceInSecond;
        if ($diff == 1) {
            return [
                'en' => "one second ago",
                "bn" => "?????? ????????????????????? ?????????"
            ];
        }

        return [
            'en' => "$diff seconds ago",
            'bn' => en2bnNumber($diff) . " ????????????????????? ?????????"
        ];
    }

    public function getInMinutes()
    {
        $diff = round($this->differenceInSecond / self::SECONDS_IN_MINUTE);
        if ($diff == 1) {
            return [
                'en' => "one minute ago",
                'bn' => "?????? ??????????????? ?????????"
            ];
        }

        return [
            'en' => "$diff minutes ago",
            "bn" => en2bnNumber($diff) . " ??????????????? ?????????"
        ];
    }

    public function getInHours()
    {
        $diff = round($this->differenceInSecond / self::SECONDS_IN_HOUR);
        if ($diff == 1) {
            return [
                'en' => "an hour ago",
                "bn" => "?????? ??????????????? ?????????"
            ];
        }

        return [
            'en' => "$diff hours ago",
            "bn" => en2bnNumber($diff) . " ??????????????? ?????????"
        ];
    }

    public function getInDays()
    {
        $diff = round($this->differenceInSecond / self::SECONDS_IN_DAY);
        if ($diff == 1) {
            return [
                'en' => "Yesterday",
                "bn" => "?????? ????????? ?????????"
            ];
        }

        return [
            'en' => "$diff days ago",
            "bn" => en2bnNumber($diff) . " ????????? ?????????"
        ];
    }

    public function getInWeeks()
    {
        $diff = round($this->differenceInSecond / self::SECONDS_IN_WEEK);
        if ($diff == 1) {
            return [
                'en' => "a week ago",
                "bn" => "?????? ?????????????????? ?????????"
            ];
        }

        return [
            'en' => "$diff weeks ago",
            "bn" => en2bnNumber($diff) . " ?????????????????? ?????????"
        ];
    }

    public function getInMonths()
    {
        $diff = round($this->differenceInSecond / self::SECONDS_IN_MONTH);
        if ($diff == 1) {
            return [
                'en' => "a month ago",
                "bn" => "?????? ????????? ?????????"
            ];
        }

        return [
            'en' => "$diff months ago",
            "bn" => en2bnNumber($diff) . " ????????? ?????????"
        ];
    }

    public function getInYears()
    {
        $diff = round($this->differenceInSecond / self::SECONDS_IN_YEAR);
        if ($diff == 1) {
            return [
                'en' => "one year ago",
                "bn" => "?????? ????????? ?????????"
            ];
        }

        return [
            'en' => "$diff years ago",
            "bn" => en2bnNumber($diff) . " ????????? ?????????"
        ];
    }

    private function getDifferenceInSeconds($time_ago)
    {
        return time() - strtotime($time_ago);
    }
}
