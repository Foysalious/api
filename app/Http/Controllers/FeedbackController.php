<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheba\Dal\Feedback\FeedbackRepository;

class FeedbackController extends Controller
{
    public function create(Request $request, FeedbackRepository $repo)
    {
        $this->validate($request, [
            'description' => 'required|string'
        ]);

        $data = $request->all();
        unset($data["remember_token"], $data["partner"], $data["manager_resource"]);
        $repo->create($data);
        return api_response($request, null, 200);
    }
}
