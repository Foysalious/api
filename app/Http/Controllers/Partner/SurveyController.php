<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Sheba\Survey\Exception\SurveyException;
use App\Sheba\Survey\SurveyTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\SurveyInterface;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;
use Sheba\Survey\SurveyService;


class SurveyController extends Controller
{
    /**
     * @param Request $request
     * @param SurveyService $surveyService
     * @return JsonResponse
     * @throws InvalidKeyException
     */
    public function getQuestions(Request $request, SurveyService $surveyService): JsonResponse
    {
        $this->validate($request, [
            'key' => 'required|in:' . implode(',', SurveyTypes::get())
        ]);
        /** @var SurveyInterface $surveyClass */
        $surveyClass = $surveyService->setKey($request->key)->get();
        $questions = $surveyClass->getQuestions();
        return api_response($request, null, 200, ['questions' => $questions]);
    }

    /**
     * @param Request $request
     * @param SurveyService $surveyService
     * @return JsonResponse
     * @throws InvalidKeyException
     */
    public function storeResult(Request $request, SurveyService $surveyService): JsonResponse
    {
        try {
            $this->validate($request, [
                'result' => 'required',
                'key' => 'required|in:' . implode(',', SurveyTypes::get())
            ]);

            $partner = $request->partner;
            /** @var SurveyInterface $surveyClass */
            $surveyClass = $surveyService->setKey($request->key)->get();
            $surveyClass->setUser($partner)->storeResult($request->result);
            return api_response($request, null, 200, ["message" => [
                "body" => "আপনার ব্যবসার প্রাথমিক তথ্য প্রদান সফল হয়েছে।",
                "title" => "তথ্য প্রদান সফল হয়েছে!"
            ]]);
        } catch (SurveyException $e) {
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        }
    }
}