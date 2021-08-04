<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\EMI\Repository as EMIRepository;
use Sheba\EMI\RequestFilter;
use Sheba\EMI\Statics;

class EmiController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $data = Statics::homeData();
            return api_response($request, $data, 200, $data);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function indexV3(Request $request): JsonResponse
    {
        try {
            $data = Statics::homeV3Data();
            return api_response($request, $data, 200, $data);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function emiList(EmiRepository $repository) {

        $request = RequestFilter::get();

        try {
            if ($request->isRecent()) {
                $data = $repository->setPartner($request->getPartner())->getRecent();
            } else {
                $data = $repository->setPartner($request->getPartner())->setOffset($request->getOffset())->setLimit($request->getLimit());
                if ($request->hasQuery()) {
                    $data = $data->setQuery($request->getQuery());
                }
                $data = $data->get();
            }
            return api_response($request->original(), null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request->original(), null, 500);
        }
    }

    public function details(Request $request, $partner_id, $id, EMIRepository $repository) {
        try {
            $request = RequestFilter::get();
            $data    = $repository->setPartner($request->getPartner())->details((int)$id);
            if ($data)
                return api_response($request->original(), $data, 200, ['data' => $data]);
            return api_response($request->original(), null, 404);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request->original(), null, 500);
        }
    }
}
