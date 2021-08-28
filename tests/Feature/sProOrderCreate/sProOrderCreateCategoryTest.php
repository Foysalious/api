<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\Partner;
use App\Models\PartnerResource;
use Sheba\Dal\Category\Category;
use Tests\Feature\FeatureTestCase;
use Illuminate\Support\Facades\DB;

class sProOrderCreateCategoryTest extends FeatureTestCase
{
    public function setUp()
    {

        parent::setUp();

        $this->truncateTable(PartnerResource::class);

        $this->truncateTable(Category::class);

        DB::table('category_partner_resource')->truncate();

        $this->logIn();

        $master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $master_category->id,
            'publication_status' => 1
        ]);

        $this->partner ->update([
            'geo_informations' => '{"lat":"23.814800953807","lng":"90.362328935888","radius":"1000"}'
        ]);

        DB::insert('insert into category_partner_resource(partner_resource_id,category_id) values (?, ?)', [1, 1]);

    }

    public function testSProCategoryAPIWithValidPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v2/resources/partner/categories?lat=23.814800953807&lng=90.362328935888', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        dd($data);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals('Kazi Fahd Zakwan', $data["profile"]["name"]);
    }

}
