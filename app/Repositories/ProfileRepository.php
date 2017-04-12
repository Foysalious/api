<?php


namespace App\Repositories;


use App\Models\Profile;

class ProfileRepository
{
    /**
     * Check if Profile already exists
     * @param $data
     * @param $queryColumn
     * @return bool
     */
    public function ifExist($data, $queryColumn)
    {
        $profile = Profile::where($queryColumn, $data)->first();
        if ($profile != null) {
            return $profile;
        } else {
            return false;
        }
    }
}