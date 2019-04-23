<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessMember;
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
                'no_employee' => 'required|integer',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'address' => 'required|string',
            ]);
            $member = Member::find($member);
            $this->setModifier($member);
            $business_data = [
                'name' => $request->name,
                'sub_domain' => $this->guessSubDomain($request->name),
                'employee_size' => $request->no_employee,
                'geo_informations' => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]),
                'address' => $request->address,
            ];
            $business = Business::create($this->withCreateModificationField($business_data));
            $member_business_data = [
                'business_id' => $business->id,
                'member_id' => $member->id,
                'type' => 'Admin',
                'join_date' => Carbon::now(),
            ];
            BusinessMember::create($this->withCreateModificationField($member_business_data));

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
