<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Sheba\Survey\SurveyTypes;
use Illuminate\Http\Request;
use Sheba\Repositories\Interfaces\SurveyInterface;
use Sheba\Survey\SurveyService;


class SurveyController extends Controller
{
    public function getQuestions(Request $request, SurveyService $surveyService)
    {
        $this->validate($request, [
            'key' => 'required|in:' . implode(',', SurveyTypes::get())
        ]);
        /** @var SurveyInterface $surveyClass */
        $surveyClass = $surveyService->setKey($request->key)->get();
        $questions = $surveyClass->getQuestions();
        return api_response($request, null, 200, ['questions' => $questions]);
    }

    public function storeResult(Request $request, SurveyService $surveyService)
    {
        $this->validate($request, [
            'result' => 'required',
            'key' => 'required|in:' . implode(',', SurveyTypes::get())
        ]);

        $partner = $request->partner;
        /** @var SurveyInterface $surveyClass */
        $surveyClass = $surveyService->setKey($request->key)->get();
        $surveyClass->setUser($partner)->storeResult($request->result);
        return api_response($request, null, 200);
    }
}