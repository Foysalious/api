<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\ChannelService;
use Illuminate\Http\Request;




class ChannelController extends Controller
{

    private $channelService;

    public function __construct(ChannelService $channelService)
    {
        $this->channelService = $channelService;
    }

    public function index(Request $request)
    {

        $units = $this->channelService->getAll();
        return http_response($request, null, 200, $units);

    }
}


