<?php

namespace App\Sheba\QRPayment;

class QRPaymentStatics
{
    const MTB_VALIDATE_URL = "retailfinqr/wqr/api/gettxndata?";
    const MTB_ACCOUNT_STATUS = "retailfin/api/Enquiry/getAccountOpenStatus/";
    const MTB_SAVE_PRIMARY_INFORMATION = "retailfin/api/acctOpen/savePrimaryInformation";
    const MTB_TOKEN_GENERATE = "retailfin/api/token";
    const MTB_SAVE_TRANSACTION_INFORMATION = "retailfin/api/acctOpen/saveTransactionInformation";
    const MTB_SAVE_NOMINEE_INFORMATION = "retailfin/api/acctOpen/saveNomineeInfo";
    const MTB_DOCUMENT_UPLOAD = "retailfin/api/acctOpen/documentUpload";

    public static function gatewayVisibleKeys(): array
    {
        return ['name', 'name_bn', 'asset', 'method_name', 'icon'];
    }

    public static function getValidationForQrGenerate(): array
    {
        return [
            "type" => 'required|in:pos_order,accounting_due',
            "type_id" => "required",
            'amount' => 'required|numeric',
            'payer_id' => 'required',
            'payer_type' => 'required|in:pos_customer,supplier',
            "payment_method" => 'required'
        ];
    }

    public static function qrGenerateKeys(): array
    {
        return array_keys(self::getValidationForQrGenerate());
    }

    public static function getValidationForValidatePayment(): array
    {
        return [
            "qr_id" => "sometimes",
            "merchant_id" => "required",
            "amount" => "required",
            "status" => "sometimes"
        ];
    }
}
