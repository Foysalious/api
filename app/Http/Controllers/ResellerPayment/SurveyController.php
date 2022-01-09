<?php namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\Survey\EloquentImplementation as Survey;

class SurveyController extends Controller
{
    public function getQA(Request $request)
    {
        $questions = config('survey.basic_questions');
        return api_response($request, null, 200, ['questions' => $questions]);
    }

    public function storeQA(Request $request)
    {
        $qa = $request->qa;
        $partner = $request->auth_user->getPartner();
        $data = [
            'user_id' => $partner->id,
            'user_type' => get_class($partner),
            'key' => 'pgw_transaction_ssl',
            'result' => $qa,
        ];
        Survey::create($data);
        return api_response($request, null, 200);
    }
}