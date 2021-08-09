<?php namespace Tests\Feature\sProServiceBookCategory;

use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Tests\Feature\FeatureTestCase;

class sProSubCategoryTest extends FeatureTestCase
{
    private $category;
    private $secondaryCategory;
    private $category_location;
    private $category_location2;
    private $name = 'Good Fix';
    private $bn_name = 'গাড়ি সার্ভিসিং';
    private $thumb = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/thumbs/1622561887_goodfix.jpg';
    private $app_thumb = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/thumbs/1622561887_goodfix.jpg';
    private $icon_png = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/icons_png/1622561888_goodfix.png';
    private $icon_svg  = 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/icons_svg/1622561888_goodfix.svg';

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Category::class);

        $this->truncateTable(CategoryLocation::class);

        $this->logIn();

    }

    public function testSProSubCategoryAPIWithoutLocationParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["categories"][0]["id"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->bn_name, $data["categories"][0]["bn_name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->app_thumb, $data["categories"][0]["app_thumb"]);
        $this->assertEquals(null, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProSubCategoryAPIWithValidLatLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lat=23.788994076131&lng=90.410852011945');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["categories"][0]["id"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->bn_name, $data["categories"][0]["bn_name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->app_thumb, $data["categories"][0]["app_thumb"]);
        $this->assertEquals(null, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProSubCategoryAPIWithValidLocationParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?location=4');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["categories"][0]["id"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->bn_name, $data["categories"][0]["bn_name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->app_thumb, $data["categories"][0]["app_thumb"]);
        $this->assertEquals(null, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProSubCategoryAPIWithInvalidLatAndValidLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lat=dfdsfasdf&lng=90.410852011945');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);
    }

    public function testSProSubCategoryAPIWithValidLatAndInvalidLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lat=23.788994076131&lng=dfdsfasdf');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProSubCategoryAPIWithInvalidLatLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lat=ghdsfsf&lng=dfdsfasdf');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng must be a number.', $data["message"]);

    }

    public function testSProSubCategoryAPIWithInvalidLocationParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?location=fdsfsg');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The location must be a number.', $data["message"]);

    }

    public function testSProSubCategoryAPIWithLatAndWithoutLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lat=23.788994076131');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng field is required when lat is present.', $data["message"]);

    }

    public function testSProSubCategoryAPIWithoutLatAndWithLngParameter()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lng=90.410852011945');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required when lng is present.', $data["message"]);

    }

    public function testSProSubCategoryAPIWithPublicationStatusZero()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 0,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 0,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["categories"][0]["id"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->bn_name, $data["categories"][0]["bn_name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->app_thumb, $data["categories"][0]["app_thumb"]);
        $this->assertEquals(null, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProSubCategoryAPIWithInvalidMasterCategoryId()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/123456/sub-categories');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProSubCategoryAPIWithLatLngOfLocationNotAvailableForThisCategory()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?lat=24.85655705&lng=89.36549165');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["categories"][0]["id"]);
        $this->assertEquals($this->name, $data["categories"][0]["name"]);
        $this->assertEquals($this->bn_name, $data["categories"][0]["bn_name"]);
        $this->assertEquals($this->thumb, $data["categories"][0]["thumb"]);
        $this->assertEquals($this->app_thumb, $data["categories"][0]["app_thumb"]);
        $this->assertEquals(null, $data["categories"][0]["icon"]);
        $this->assertEquals($this->icon_png, $data["categories"][0]["icon_png"]);
        $this->assertEquals($this->icon_svg, $data["categories"][0]["icon_svg"]);

    }

    public function testSProSubCategoryAPIWithLocationNotAvailableForThisCategory()
    {
        //arrange
        $this->category = factory(Category::class)->create([
            'publication_status' => 1,
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => $this->name,
            'bn_name' => $this->bn_name,
            'parent_id' => $this->category->id,
            'publication_status' => 1,
            'thumb' => $this->thumb,
            'app_thumb' => $this->app_thumb,
            'icon_png' => $this->icon_png,
            'icon_svg' => $this->icon_svg
        ]);

        $this->category_location = factory(CategoryLocation::class)->create([
            'category_id' => $this->category->id,
            'location_id' => 4
        ]);

        $this->category_location2 = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => 4
        ]);

        //act
        $response = $this->get('v3/categories/' . $this->category->id . '/sub-categories?location=8');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }


}
