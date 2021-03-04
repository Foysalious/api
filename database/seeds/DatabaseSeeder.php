<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * @var array
     */
    protected $commonSeeds;

    public function __construct()
    {
        $this->commonSeeds = [
            'created_by' => 1,
            'created_by_name' => 'IT - Shafiqul Islam',
            'updated_by' => 1,
            'updated_by_name' => 'IT - Shafiqul Islam',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->clearDatabase();
        $this->call(LocationsTableSeeder::class);
    }

    private function clearDatabase()
    {
        $db_name = env('DB_DATABASE');
        $q = collect(DB::select(
            "SELECT CONCAT('TRUNCATE TABLE ', TABLE_NAME, ';') as part
             FROM INFORMATION_SCHEMA.TABLES 
             WHERE table_schema IN ('$db_name')
             AND TABLE_NAME <> 'migrations';
        "))->pluck('part')->implode(' ');


        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::unprepared($q);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
