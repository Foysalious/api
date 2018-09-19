<?php namespace Sheba\Helpers\Formatters;

class TakaFormatter
{
    public static function formatTaka($amount, $comma_separation = false, $comma_separation_format = "BDT")
    {
        $amount = number_format($amount, 2, '.', '');
        if ($comma_separation) {
            $amount = self::commaSeparate($amount, $decimal = 2, $comma_separation_format);
        }
        return $amount;
    }

    public static function commaSeparate($amount, $decimal = 0, $format = "BDT")
    {
        $negate = false;
        if ($amount < 0) {
            $amount = substr($amount, 1);
            $negate = true;
        }

        $amount = is_numeric($amount) ? (string)$amount : $amount;

        $n_amount = [];
        if ($format == "BDT") {
            // 1,00,000,00,00,00,000
            $comma_positions = [4, 6, 8, 10, 13, 15];
        } else {
            // 100,000,000,000,000
            $comma_positions = [4, 7, 10, 13, 16, 19];
        }

        if ($decimal) {
            foreach ($comma_positions as $key => $item) {
                $comma_positions[$key] += ($decimal + 1);
            }
        }

        for ($i = strlen($amount) - 1, $r = 1; $i >= 0; $i--, $r++) {
            if (in_array($r, $comma_positions)) $n_amount[] = ",";

            $n_amount[] = $amount[$i];
        }

        $result = strrev(implode('', $n_amount));
        return ($negate) ? ("-" . $result) : $result;
    }

    /**
     * Return shorthand currency format.
     *
     * @param $amount
     * @param $precision = 1
     * @return string
     */
    public static function currencyShortenFormat($amount, $precision = 1)
    {
        if ($amount < 1000) {
            $amount_format = number_format($amount);
        } else if ($amount < 1000000) { // Anything less than a million
            $amount_format = number_format($amount / 1000, $precision) . 'K';
        } else if ($amount < 1000000000) { // Anything less than a billion
            $amount_format = number_format($amount / 1000000, $precision) . 'M';
        } else { // At least a billion
            $amount_format = number_format($amount / 1000000000, $precision) . 'B';
        }

        return $amount_format;
    }
}