<?php namespace Sheba\TopUp\Gateway\FailedReason;

class SslRechargeResponseCodes
{
    public static function messages()
    {
        return [
            '100' => "The Recharge request has been created but is waiting for initiation.",
            '200' => "Recharge request has been initiated for processing.",
            '201' => "Processing has started.",
            '202' => "Recharge request has been successfully submitted to Operator for processing.",
            '203' => "This recharge will be retried as it had not been successful in first try.",
            '300' => "Error while in process.",
            '301' => "IN Error.",
            '302' => "The recharge recipient phone number is invalid.",
            '303' => "The recharge recipient phone number is not prepaid type.",
            '304' => "The recharge amount is either invalid or out of operator assigned valid range.",
            '305' => "This recharge recipient phone number is barred by operator.",
            '306' => "This recharge recipient phone number has crossed the maximum recharge limit.",
            '307' => "This recharge has been denied because it was within consecutive recharge timeinterval limit.",
            '308' => "IN Connectivity Error.",
            '309' => "You do not have sufficient stock to do this recharge.",
            '310' => "The Recharge service unavailable at this moment. Pls try again later.",
            '311' => "The Recharge could not be processed due to a technical issue. Pls try again later.",
            '312' => "The MSISDN and Amount could not be verified.",
            '313' => "You have provided an invalid password.",
            '314' => "External Server Connection Authentication Failed.",
            '315' => "External Entity Not Found.",
            '316' => "Invalid information for External Entity.",
            '317' => "External Entity Suspended.",
            '318' => "Invalid XML Format.",
            '319' => "The Recharge MSISDN or Amount id in invalid format.",
            '320' => "The Recharge MSISDN Prefix could not be found.",
            '321' => "Location is not allowed for External Entity.",
            '322' => "External Entity is not Associated with any Partner.",
            '323' => "External Entity Credit Limit not defined.",
            '324' => "External Entity Max Daily Credit Limit Reached.",
            '325' => "External Entity Max Weekly Credit Limit Reached.",
            '326' => "External Entity Max Monthly Credit Limit Reached.",
            '327' => "Invalid Message.",
            '328' => "Subscriber Not Found.",
            '329' => "Subscriber is barred.",
            '330' => "Caught recharge with same amount within Successful Recharge Block Time.",
            '331' => "Service Unavailable.",
            '332' => "IN Response Time out (Ambiguous Case).",
            '333' => "Internal System Error.",
            '334' => "Account number not found.",
            '335' => "Account Expired.",
            '336' => "Used for first time or invalid.",
            '337' => "Subscriber not activated.",
            '338' => "No PPS information.",
            '339' => "Recharge fail.",
            '340' => "Transaction SN repeated.",
            '341' => "Recharge succeeded but login failed.",
            '342' => "Querying area code failed (reversed but currently not in use).",
            '343' => "Querying the validity period corresponding to the recharge amount failed.",
            '344' => "Reaching the max of registered subscribers.",
            '345' => "Service data is not configured.",
            '346' => "Invalid Connection Type. Please use prepaid or postpaid as field value.",
            '347' => "This operator is not supported at the moment.",
            '348' => "This operator is unknown.",
            '349' => "This GUID has already been used. Please provide an unique GUID.",
            '350' => "This GUID is invalid. Please combine alphanumeric characters of length 25 forGUID.",
            '351' => "This GUID could not be found. Pls try with a vaild Recharge Request GUID.",
            '352' => "This recharge cycle has ended. It cannot be initiated or cancelled.",
            '353' => "This MSISDN has already been recharged once moments ago.",
            '354' => "Your account with VR is currently inactive.",
            '380' => "This Recharge could not be confirmed and is tagged as permanent failure.",
            '398' => "General Exception.",
            '399' => "Unknown Status due to Technical fault or any other general or critical error.",
            '400' => "This Recharge has been canceled by the client.",
            '800' => "Test Successful Recharge. Recharge is marked as complete for testing purpose.",
            '900' => "Successful Recharge.",
            '999' => "Other"
        ];
    }

    public static function getSuccessfulCode()
    {
        return '0000';
    }
}


