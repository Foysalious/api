<?php

class LocationsTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = ["Bangladesh"];

        foreach($countries as $country) {
            DB::table('countries')->insert(array_merge($this->commonSeeds, [
                'name' => $country
            ]));
        }

        $cities = ["Dhaka"];

        foreach($cities as $city) {
            DB::table('cities')->insert(array_merge($this->commonSeeds, [
                'name' => $city,
                'country_id' => 1
            ]));
        }

        $locations = ["Mohammadpur", "Farmgate", "Dhanmondi", "Gulshan"];

        foreach($locations as $location) {
            DB::table('locations')->insert(array_merge($this->commonSeeds, [
                'name' => $location,
                'city_id' => 1,
                'publication_status' => 1,

            ]));
        }
    }
}
