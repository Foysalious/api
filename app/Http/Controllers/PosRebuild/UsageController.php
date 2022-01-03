<?php namespace App\Http\Controllers\PosRebuild;

use App\Exceptions\NotFoundException;
use App\Models\Partner;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Sheba\Usage\Usage;

class UsageController extends Controller
{
    /**
     * @throws NotFoundException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'partner_id' => 'required|int',
            'usage_type' => 'required|string',
        ]);
        $partner = Partner::find($request->partner_id);
        if (!$partner) throw new NotFoundException('Partner not found.', 404);
        (new Usage())->setUser($partner)->setType($request->usage_type)->create($partner);
        return http_response($request, null, 200);

    }
}
