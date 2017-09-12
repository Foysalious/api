<?php

namespace App\Repositories;


class AffiliateRepository
{

    public function sortAgents($request, $agents)
    {
        if ($request->has('sort')) {
            if ($request->sort != 'name') {
                array_multisort(array_column($agents, $request->sort), SORT_DESC, $agents);
            } else {
                array_multisort(array_column($agents, $request->sort), SORT_STRING, $agents);
            }
        } else {
            array_multisort(array_column($agents, $request->sort), SORT_STRING, $agents);
        }
        return $agents;
    }
}