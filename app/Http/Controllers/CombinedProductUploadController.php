<?php

namespace App\Http\Controllers;

use App\Sheba\FacebookCatalogue\CombinedCatalogue;
use App\Sheba\Reports\ProductCategory;
use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\Reports\Product;

class CombinedProductUploadController extends Controller
{
    public function upload(Product $product, ProductCategory $productCategory, CombinedCatalogue $combinedCatalogue)
    {
        $services = $product->calculate()->get();
        $categories = $productCategory->calculate()->get();
        $combinedCatalogue->setServices($services)->setCategories($categories)->upload();
        return ['code' => '200'];
    }
}
