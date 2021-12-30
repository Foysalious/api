<?php

namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\DigitalCollectionSetting\Model as DigitalCollectionSetting;
use Sheba\Dal\PgwStore\Model as PgwStore;
use Sheba\EMI\Banks;
use Sheba\EMI\CalculatorForManager;
use Sheba\ModificationFields;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\PaymentLink\PaymentLinkStatus;

class PaymentServiceController extends Controller
{
    use ModificationFields;

    public function getPaymentGateway(Request $request, PgwStore $pgwStore)
    {
        try {
            $pgwData = [];
            $partner = Partner::where('id', $request->partner->id)->first();
            $partner_account = $partner->pgwStoreAccounts()->published()->first();

            $pgwStores = $pgwStore->select('id', 'name', 'key', 'name_bn', 'icon')->get();

            if ($partner_account) {
                foreach ($pgwStores as $pgwStore) {
                    $pgwData[] = [
                        'id' => $pgwStore->id,
                        'name' => $pgwStore->name,
                        'key' => $pgwStore->key,
                        'name_bn' => $pgwStore->name_bn,
                        'icon' => $pgwStore->icon,
                        'status' => PaymentLinkStatus::ACTIVE
                    ];
                }
            } else {
                foreach ($pgwStores as $pgwStore) {
                    $pgwData[] = [
                        'id' => $pgwStore->id,
                        'name' => $pgwStore->name,
                        'key' => $pgwStore->key,
                        'name_bn' => $pgwStore->name_bn,
                        'icon' => $pgwStore->icon,
                        'status' => PaymentLinkStatus::INACTIVE
                    ];
                }
            }
            return api_response($request, null, 200, ['data' => $pgwData]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentServiceCharge(Request $request)
    {
        try {
            $partnerId = $request->partner->id;
            $digitalCollection = DigitalCollectionSetting::where('partner_id', $partnerId)->select('service_charge')->first();

            $data = PaymentLinkStatics::customPaymentServiceData();
            if ($digitalCollection) {
                $data['current_percentage'] = $digitalCollection->service_charge;
            } else {
                $data['current_percentage'] = PaymentLinkStatics::SERVICE_CHARGE;
            }

            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePaymentServiceCharge(Request $request)
    {
        try {
            $this->validate($request, [
                "current_percentage" => "required | numeric"
            ]);

            $digitalCollectionSetting = new DigitalCollectionSetting();
            $partnerId = $request->partner->id;
            $partner = $digitalCollectionSetting->where('partner_id', $partnerId)->first();

            if (!$partner) {
                $digitalCollectionSetting->partner_id = $request->partner->id;
                $digitalCollectionSetting->service_charge = $request->current_percentage;
                $this->withCreateModificationField($digitalCollectionSetting);
                $digitalCollectionSetting->save();
            } else {
                $data = ['service_charge' => $request->current_percentage];
                $digitalCollectionSetting->query()->where('partner_id', $partnerId)
                    ->update($this->withUpdateModificationField($data));
            }

            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param CalculatorForManager $emi_calculator
     * @return \Illuminate\Http\JsonResponse
     */
    public function emiInformationForManager(Request $request, CalculatorForManager $emi_calculator)
    {
        try {
            $partner = $request->partner;

            $this->validate($request, ['amount' => 'required|numeric|min:' . config('emi.manager.minimum_emi_amount')]);
            $amount       = $request->amount;
            $icons_folder = getEmiBankIconsFolder(true);
            $emi_data     = [
                "emi"   => $emi_calculator->setPartner($partner)->getCharges($amount),
                "banks" => (new Banks())->setAmount($amount)->get(),
                "minimum_amount" => number_format(config('sheba.min_order_amount_for_emi')),
                "static_info"    => [
                    "how_emi_works"        => [
                        "EMI (Equated Monthly Installment) is one of the payment methods of online purchasing, only for the customers using any of the accepted Credit Cards on Sheba.xyz.* It allows customers to pay for their ordered services  in easy equal monthly installments.*",
                        "Sheba.xyz has introduced a convenient option of choosing up to 12 months EMI facility for customers who use Credit Cards for buying services worth BDT 5,000 or more. The duration and extent of the EMI options available will be visible on the payment page after order placement. EMI plans are also viewable on the checkout page in the EMI Banner below the bill section.",
                        "Customers wanting to avail EMI facility must have a Credit Card from any one of the banks in the list shown in the payment page.",
                        "EMI facilities available for all services worth BDT 5,000 or more.",
                        "EMI charges may vary on promotional offers.",
                        "Sheba.xyz  may charge additional convenience fee if the customer extends the period of EMI offered."
                    ],
                    "terms_and_conditions" => [
                        "As soon as you complete your purchase order on Sheba.xyz, you will see the full amount charged on your credit card.",
                        "You must Sign and Complete the EMI form and submit it at Sheba.xyz within 3 working days.",
                        "Once Sheba.xyz receives this signed document from the customer, then it shall be submitted to the concerned bank to commence the EMI process.",
                        "The EMI processing will be handled by the bank itself *. After 5-7 working days, your bank will convert this into EMI.",
                        "From your next billing cycle, you will be charged the EMI amount and your credit limit will be reduced by the outstanding amount.",
                        "If you do not receive an updated monthly bank statement reflecting your EMI transactions for the following month, feel free to contact us at 16516  for further assistance.",
                        "For example, if you have made a 3-month EMI purchase of BDT 30,000 and your credit limit is BDT 1, 00,000 then your bank will block your credit limit by BDT 30,000 and thus your available credit limit after the purchase will only be BDT 70,000. As and when you pay your EMI every month, your credit limit will be released accordingly.",
                        "EMI facilities with the aforesaid Banks are regulated as per their terms and conditions and these terms may vary from one bank to another.",
                        "For any query or concern please contact your issuing bank, if your purchase has not been converted to EMI by 7 working days of your transaction date."
                    ]
                ]
            ];

            return api_response($request, null, 200, ['price' => $amount, 'info' => $emi_data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}