<?php namespace Sheba\TransactionValidators;

class BKashTransactionCodes
{
    public static function messages()
    {
        return [
            '0000' => "trxID is valid and transaction is successful.",
            '0010' => "trxID is valid but transaction is in pending state.",
            '0011' => "trxID is valid but transaction is in pending state.",
            '0100' => "trxID is valid but transaction has been reversed.",
            '0111' => "trxID is valid but transaction has failed.",
            '1001' => "Invalid MSISDN input. Try with correct mobile no.",
            '1002' => "Invalid trxID, it does not exist.",
            '1003' => "Access denied. Username or Password is incorrect.",
            '1004' => "Access denied. trxID is not related to this username.",
            '2000' => "Access denied. User does not have access to this module.",
            '2001' => "Access denied. User date time request is exceeded of the defined limit.",
            '3000' => "Missing required mandatory fields for this module Missing fields Error",
            '4001' => "Duplicate request. Consecutive hit for the same request within 5 minutes.",
            '9999' => "Could not process request."
        ];
    }

    public static function getSuccessfulCode()
    {
        return '0000';
    }
}