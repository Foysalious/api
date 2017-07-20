<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Navigation extends Eloquent
{
    protected $connection = 'mongodb';

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function services()
    {
        $final = [];
        foreach ($this->groups as $group) {
            $service_id = collect($group->services)->pluck('id')->toArray();
            foreach ($service_id as $service) {
                $service = Service::with(['category' => function ($q) {
                    $q->select('id', 'name');
                }])->select('id', 'slug', 'category_id', 'name', 'banner', 'variable_type', 'variables')->where('id', $service)->first();
                array_push($final, $service);
            }
        }
        return $final;
    }
}
