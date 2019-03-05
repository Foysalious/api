<?php namespace Sheba\AppSettings\HomeGrids;

use App\Models\Block;
use App\Models\Grid;
use App\Models\HomeGrid;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Sheba\AppSettings\HomePageSetting\Cacher;
use Sheba\ModificationFields;

class Handler
{
    use ModificationFields;

    private $cacher;

    public function __construct(Cacher $cacher)
    {
        $this->cacher = $cacher;
    }

    public function save($data)
    {
        $created_counter = 0;
        $updated_counter = 0;

        $new_data = [];
        foreach ($data as $key => $item){
            $order = $key + 1;
            $new_data[] = [
                'item_type' => "App\\Models\\" . $item['name'],
                'item_id'   => $item['_sub_item']['id'],
                'order'     => $order,
                'is_published_for_app' => 1,
                'is_published_for_web' => 1
            ];
        }
            $stored_home_grid_data = HomeGrid::publishedForApp()->ordered()->get();

        $to_delete = $stored_home_grid_data->pluck('order')->diff(collect($new_data)->pluck('order'));
        HomeGrid::whereIn('order', $to_delete->toArray())->delete();

        foreach ($new_data as $key => $data){
            if ($stored_home_grid_data->contains('order', $data['order'])) {
                $db_home_grid = $stored_home_grid_data->where('order', $data['order'])->first();
                if ($db_home_grid->item_type != $data['item_type'] || $db_home_grid->item_id != $data['item_id']) {
                    $db_home_grid->update($this->withUpdateModificationField($data));
                    $updated_counter++;
                }
            } else {
                HomeGrid::insert($this->withBothModificationFields($data));
                $created_counter++;
            }
        }

        $this->cacher->update();

        $deleted_counter = $to_delete->count();
        return "$created_counter items created & $updated_counter items updated & $deleted_counter items deleted.";
    }

    public function saveGrid($data)
    {
        $grid = Grid::create($this->withBothModificationFields([
            'title' => $data->grid_title,
            'is_published' => 1,
            'attributes' => null
        ]));

        $portals = explode(",", $data->grid_portal_name);
        $screens = explode(",",$data->grid_portal_screen);

        foreach ($portals as $portal_name){
            foreach ($screens as $portal_screen) {
                DB::table('grid_portal')->insert([
                    'grid_id' => $grid->id,
                    'portal_name' => $portal_name,
                    'screen' => $portal_screen
                ]);
            }
        }

        foreach ($data->data as $key => $item) {
            $order = $key + 1;
            $new_data = [
                'item_type' => "App\\Models\\" . $item['name'],
                'item_id'   => $item['_sub_item']['id'],
            ];
            $block = Block::where('item_id',$new_data['item_id'])->where('item_type',$new_data['item_type']);
            if($block->exists()) {
                $block = $block->first();
                $block->item_type = $new_data['item_type'];
                $block->item_id = $new_data['item_id'];
                $block->save();
                $locations = Location::where('city_id',$data->grid_city)->pluck('id')->toArray();
                foreach ($locations as $location) {
                    $grid->blocks()->attach($block->id, ["location_id"=>$location, "order" => $order]);
                }
            } else {
                $block = Block::insert($this->withBothModificationFields($new_data));
                $locations = Location::where('city_id',$data->grid_city)->pluck('id')->toArray();
                foreach ($locations as $location) {
                    $grid->blocks()->attach($block->id, ["location_id"=>$location, "order" => $order]);
                }
            }
        }

        return "Grid created successfully";
    }


    public function updateGrid($grid_id, $data)
    {
        $grid = Grid::where('id',$grid_id)->first();

        DB::table('grid_portal')->where("grid_id",$grid->id)->delete();

        $portals = explode(",", $data->grid_portal_name);
        $screens = explode(",",$data->grid_portal_screen);

        foreach ($portals as $portal_name){
            foreach ($screens as $portal_screen) {
                if(!(DB::table('grid_portal')->where('grid_id',$grid_id)->where('portal_name',$portal_name)->where('screen',$portal_screen)->exists())) {
                    DB::table('grid_portal')->insert([
                        'grid_id' => $grid->id,
                        'portal_name' => $portal_name,
                        'screen' => $portal_screen
                    ]);
                }

            }
        }
        $grid->blocks()->detach();
        foreach ($data->data as $key => $item) {
            $order = $key + 1;
            $new_data = [
                'item_type' => "App\\Models\\" . $item['name'],
                'item_id'   => $item['_sub_item']['id'],
            ];
            $block = Block::where('item_id',$new_data['item_id'])->where('item_type',$new_data['item_type']);
            if($block->exists()) {
                $block = $block->first();
                $block->item_type = $new_data['item_type'];
                $block->item_id = $new_data['item_id'];
                $block->save();
                $locations = Location::where('city_id',$data->grid_city)->pluck('id')->toArray();
                foreach ($locations as $location) {
                    $grid->blocks()->attach($block->id, ["location_id"=>$location, "order" => $order]);
                }
            } else {
                $block = Block::insert($this->withBothModificationFields($new_data));
                $locations = Location::where('city_id',$data->grid_city)->pluck('id')->toArray();
                foreach ($locations as $location) {
                    $grid->blocks()->attach($block->id, ["location_id"=>$location, "order" => $order]);
                }
            }
        }
        return "Grid updated successfully";
    }

}