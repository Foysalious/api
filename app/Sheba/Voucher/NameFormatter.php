<?php

namespace Sheba\Voucher;


class NameFormatter
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function format()
    {
        $format_name = '';
        for ($i = 0; $i < strlen($this->name); $i++) {
            if (utf8_decode($this->name[$i]) == '?') {
                continue;
            }
            $format_name .= $this->name[$i];
        }
        return $format_name;
    }
}