<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\Member;
use Illuminate\Validation\ValidationException;
use App\Models\PartnerBankInformation;
use Sheba\FileManagers\CdnFileManager;
use App\Http\Controllers\Controller;
use App\Repositories\FileRepository;
use App\Http\Requests\SpLoanRequest;
use Sheba\FileManagers\FileManager;
use App\Models\PartnerBankLoan;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Profile;
use Carbon\Carbon;
use DB;

class MembersController extends Controller
{
    use ModificationFields;

    public function updateBusinessInfo($member, Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'no_employee' => 'required|numeric',
                'location' => 'required|numeric',
                'address' => 'required|string',
            ]);
            $member = Member::find($member);
            $sub_domain = $this->guessSubDomain($request->name);
            dd($sub_domain);

            $data = [
                'company_name' => $request->name,
                'no_employee' => $request->no_employee,
                'location' => $request->location,
                'address' => $request->address,
            ];

            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function guessSubDomain($name)
    {
        $blacklist = ["google", "facebook", "microsoft", "sheba", "sheba.xyz"];
        $base_name = $name = preg_replace('/-$/', '', substr(strtolower(clean($name)), 0, 15));
        $already_used = Business::select('sub_domain')->lists('sub_domain')->toArray();
        $counter = 0;
        while (in_array($name, array_merge($blacklist, $already_used))) {
            $name = $base_name . $counter;
            $counter++;
        }
        return $name;
    }
}
