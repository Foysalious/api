<?php namespace App\Http\Controllers\Partner;

use App\Exceptions\Pos\DataMigrationException;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Partner\DataMigration\DataMigration;

class DataMigrationController extends Controller
{
    /**
     * @param Request $request
     * @param DataMigration $dataMigration
     * @return JsonResponse
     * @throws DataMigrationException
     */
    public function migrate(Request $request, DataMigration $dataMigration)
    {
        $partner = $request->auth_user->getPartner();
        $dataMigration->setPartner($partner)->migrate();
        return http_response($request, null, 200);
    }

    public function testMigration(Request $request, DataMigration $dataMigration)
    {
        for ($partnerId = $request->partner_id_start; $partnerId <= $request->partner_id_end; $partnerId++) {
            $partner = Partner::find($partnerId);
            $dataMigration->setPartner($partner)->migrate();
        }
        return http_response($request, null, 200);
    }
}
