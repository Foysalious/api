<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ComplianceInfo\ComplianceInfo;
use Sheba\ComplianceInfo\Statics;

class ComplianceInfoController extends Controller
{
    private $compliance;

    public function __construct(ComplianceInfo $compliance)
    {
        $this->compliance = $compliance;
    }

    /**
     * @param $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function show($partner, Request $request): JsonResponse
    {
        try {
            /** @var Partner $partner */
            $partner = $request->partner;
            $data = $this->compliance->setPartner($partner)->get();
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function update($partner, Request $request): JsonResponse
    {
        try {
            $this->validate($request, Statics::complianceInfoUpdateValidation());
            $data = $request->only(Statics::complianceInfoUpdateFields());
            $this->compliance->setPartner($request->partner)->updateData($data);
            return api_response($request, null, 200, ['data' => "Compliance information updated"]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
