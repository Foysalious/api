<?php


namespace App\Http\Controllers\Accounting;


use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Repository\IconsRepository;
use Sheba\ModificationFields;

class IconsController extends Controller
{
    use ModificationFields;

    /** @var IconsRepository */
    private $iconsRepo;

    public function __construct(IconsRepository $iconsRepo) {
        $this->iconsRepo = $iconsRepo;
    }

    public function getIcons(Request $request)
    {
        try {
            $response = $this->iconsRepo->getIconList($request->partner->id);
            return api_response($request, $response, 200, ['data' => $response]);
        } catch (Exception $e) {
            return api_response(
                $request,
                null,
                $e->getCode() == 0 ? 400 : $e->getCode(),
                ['message' => $e->getMessage()]
            );
        }
    }
}