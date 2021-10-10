<?php namespace Factory {


    use App\Models\Location;

    class LocationFactory extends Factory
    {

        protected function getModelClass()
        {
            return Location::class;
        }

        protected function getData()
        {
            return array_merge($this->commonSeeds, [
                'city_id' => 1,
                'name' => $this->faker->city,
                'geo_informations' => '{"lat":23.75655,"lng":90.387215,"radius":"1.1","geometry":{"type":"Polygon","coordinates":[[[90.3898,23.75835],[90.38458,23.75791],[90.38449,23.75685],[90.38445,23.75499],[90.3855,23.75495],[90.38664,23.755],[90.38877,23.75475],[90.38967,23.7566],[90.38998,23.758],[90.3898,23.75835],[90.3898,23.75835]]]},"center":{"lat":23.75655,"lng":90.387215}}',
                'publication_status' => 1,
                'is_published_for_partner' => 1,

            ]);
        }
    }
}