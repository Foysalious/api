<?php namespace Sheba\Business\Procurement\Code;

abstract class Machine
{
    const PROCUREMENT_CODE_LENGTH = 4;
    const BID_CODE_LENGTH = 6;
    const PAD_STRING = "0";
    const SEPARATOR = "-";

    protected static $SALES_CHANNELS;
}
