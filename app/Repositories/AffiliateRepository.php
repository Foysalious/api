<?php

namespace App\Repositories;


class AffiliateRepository
{

    public function sortAgents($request, $agents)
    {
        $sortBy = 'name';
        if ($request->has('sort')) {
            $sortBy = $request->sort;
            if ($sortBy != 'name') {
                array_multisort(array_column($agents, $sortBy), SORT_DESC, $agents);
            } else {
                array_multisort(array_column($agents, $sortBy), SORT_STRING, $agents);
            }
        } else {
            array_multisort(array_column($agents, $sortBy), SORT_STRING, $agents);
        }
        return $agents;
    }
}