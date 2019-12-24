<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Sheba\Dal\Newsletter\Model as Newsletter;
use Illuminate\Http\Request;
use Sheba\Newsletter\Creator;

class NewsletterController extends Controller
{
    public function create(Request $request, Creator $creator)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'portal_name' => 'required|string|in:' . implode(',', config('sheba.portals'))
        ]);
        $creator->setEmail($request->email)->setPortalName($request->portal_name)->setIp($request->ip);
        $creator->store();
    }
}