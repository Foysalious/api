<?php namespace App\Http\Controllers\Partner;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Partner\DataMigration\DataMigration;

class DataMigrationController extends Controller
{
    public function migrate(Request $request, DataMigration $dataMigration)
    {
        $partner = $request->auth_user->getPartner();
        $dataMigration->setPartner($partner)->migrate();
        return http_response($request, null, 200);
    }
}
