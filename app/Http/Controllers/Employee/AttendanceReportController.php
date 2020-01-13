<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Transformers\Business\Attendance\ReportTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        /*$manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new ReportTransformer());
        $announcements = $manager->createData($announcements)->toArray()['data'];
        return api_response($request, $announcements, 200, ['announcements' => $announcements]);*/
    }
}
