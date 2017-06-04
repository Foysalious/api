<?php

namespace Sheba\Voucher;


class NameFormatter
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function removeUnicodeCharactersAndFormatName()
    {
        $format_name = '';
        for ($i = 0; $i < strlen($this->name); $i++) {
            if (utf8_decode($this->name[$i]) == '?') {
                continue;
            }
            $format_name .= $this->name[$i];
        }
        return $this->formatName($format_name);
    }

    private function formatName($name)
    {
        if (preg_match("/^(Md.|Md|Mr.|Mr|Mrs.|Mrs|engr.|eng)/i", $name)) {
            return trim(preg_replace('/^(Md.|Md|Mr.|Mr|Mrs.|Mrs|engr.|eng)/i', '', $name));
        }
        return $name;
    }
}