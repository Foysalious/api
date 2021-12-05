<?php


namespace App\Http\Controllers\Accounting;


use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
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

    /**
     * @throws AccountingEntryServerError
     */
    public function getIcons(Request $request)
    {
            $response = $this->iconsRepo->getIconList($request->partner->id);
            return api_response($request, $response, 200, ['data' => $response]);
    }
}