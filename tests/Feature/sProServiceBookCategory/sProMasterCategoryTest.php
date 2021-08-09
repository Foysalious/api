<?php namespace Tests\Feature\sProServiceBookCategory;

use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Tests\Feature\FeatureTestCase;

class sProMasterCategoryTest extends FeatureTestCase
{
    private $category;
    private $category_location;
    private $name = 'Appliance Repair';
    private $thumb = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1482049279_home_appliances_.png';
    private $icon = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/icons/1618145164_tiwnn.svg';
    private $icon_png = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/icons_png/1618147294_tiwnn.png';
    private $icon_svg  = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/marketplace/default_images/svg/default_icon.svg';


    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Category::class);

        $this->truncateTable(CategoryLocation::class);

        $this->logIn();

    }

    public function testSProMasterCategoryAPIWithoutLocationParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->icon, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProMasterCategoryAPIWithValidLatLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=23.788994076131&lng=90.410852011945');

        $data = $response->decodeResponseJson();


        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->icon, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProMasterCategoryAPIWithValidLocationParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?location=4');

        $data = $response->decodeResponseJson();


        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->icon, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProMasterCategoryAPIWithInvalidLatAndValidLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=dfdsfasdf&lng=90.410852011945');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);

    }

    public function testSProMasterCategoryAPIWithValidLatAndInvalidLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=23.788994076131&lng=dfdsfasdf');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProMasterCategoryAPIWithInvalidLatLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=ghdsfsf&lng=dfdsfasdf');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng must be a number.', $data["message"]);

    }

    public function testSProMasterCategoryAPIWithInvalidLocationParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?location=fdsfsg');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The location must be a number.', $data["message"]);

    }

    public function testSProMasterCategoryAPIWithLatAndWithoutLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=23.788994076131');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng field is required when lat is present.', $data["message"]);

    }

    public function testSProMasterCategoryAPIWithoutLatAndWithLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lng=90.410852011945');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required when lng is present.', $data["message"]);

    }

    public function testSProMasterCategoryAPIWithPublicationStatusZero()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 0,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);


        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=23.788994076131&lng=90.410852011945');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->icon, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProMasterCategoryAPIWithLatLngOfLocationNotAvailableForThisCategory()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?lat=24.85655705&lng=89.36549165');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->icon, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProMasterCategoryAPIWithLocationNotAvailableForThisCategory()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => '',
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => '',
            'icon' => $this->icon,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories?location=8');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

}
