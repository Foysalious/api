<?php namespace App\Http\Controllers\B2b;

use Sheba\Repositories\Interfaces\BidRepositoryInterface;
use App\Http\Controllers\Controller;

class BidController extends Controller
{
    public function index($business,$procurement)
    {
        dd($business, $procurement);
    }
}