<?php namespace Sheba\Order\Code;

abstract class Machine
{
    protected static $ORDER_CODE_START;
    protected static $JOB_CODE_START;
    protected static $SALES_CHANNEL_PREFIXES;
    protected static $SALES_CHANNELS;

    const CHANNEL_CODE_LENGTH = 1;
    const ORDER_CODE_LENGTH = 6;
    const PARTNER_CODE_LENGTH = 4;
    const JOB_CODE_LENGTH = 8;

    const PAD_STRING = "0";
    const SEPARATOR = "-";

    public function __construct()
    {
        self::$ORDER_CODE_START = (int)config('sheba.order_code_start');
        self::$JOB_CODE_START = (int)config('sheba.job_code_start');
        self::$SALES_CHANNEL_PREFIXES = getSalesChannels('prefix');
        self::$SALES_CHANNELS = getSalesChannels('name');
    }

    protected function isValidChannel($channel)
    {
        return in_array($channel, array_values(self::$SALES_CHANNELS));
    }
}