<!DOCTYPE html>
<html>
<head>
    <title>Product Catalogue</title>
</head>
<body>
<table>
    <tr>
        <td>id</td>
        <td>custom_label_0</td>
        <td>title</td>
        <td>description</td>
        <td>product_type</td>
        <td>google_product_category</td>
        <td>facebook_product_category</td>
        <td>link</td>
        <td>image_link</td>
        <td>condition</td>
        <td>availability</td>
        <td>price</td>
        <td>brand</td>
        <td>ios_url</td>
        <td>ios_package</td>
        <td>ios_app_name</td>
        <td>android_url</td>
        <td>android_package</td>
        <td>android_app_name</td>
        <td>custom_label_1</td>
        <td>custom_label_2</td>
        <td>custom_label_3</td>
    </tr>

    @foreach($services as $service)
        <tr>
            <td>{{ $service->id }}</td>
            <td>{{ $service->category_id }}</td>
            <td>{{ $service->name }}</td>
            <td>{{ $service->short_description }}</td>
            <td>{{ ($service->category->parent?$service->category->parent->name:'') . ' > ' . $service->category->name }}</td>
            <td>{{ $service->google_product_category ? $service->google_product_category : ($service->category->parent?$service->category->parent->name:'') . ' > ' . $service->category->name }}</td>
            <td>{{ $service->facebook_product_category ? $service->facebook_product_category : ($service->category->parent?$service->category->parent->name:'') . ' > ' . $service->category->name }}</td>
            <td>{{ config('sheba.front_url'). '/' .($service->getSlug() ?? '') }}</td>
            <td>{{ $service->catalog_thumb ?: $service->app_thumb}}</td>
            <td>new</td>
            <td>in stock</td>
            <td>{{ $service->catalog_price ?: ($service->start_price ?: 0) }} BDT</td>
            <td>Sheba.xyz</td>
            <td>{{ $service->ios_url }}</td>
            <td>{{ $service->ios_app_store_id }}</td>
            <td>{{ $service->ios_app_name }}</td>
            <td>{{ $service->android_url }}</td>
            <td>{{ $service->android_app_store_id }}</td>
            <td>{{ $service->android_app_name }}</td>
            <td>{{ $service->web_link }}</td>
            <td>{{ $service->web_deeplink }}</td>
            <td>{{ $service->sub_link }}</td>
        </tr>
    @endforeach
    @foreach($categories as $category)
        <tr>
            <td>sc-{{ $category->id }}</td>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->short_description }}</td>
            <td>{{ $category->parent->name . ' > ' . $category->name }}</td>
            <td>{{ $category->google_product_category ? $category->google_product_category : ($category->parent?$category->parent->name:'') . ' > ' . $category->name }}</td>
            <td>{{ $category->facebook_product_category ? $category->facebook_product_category : ($category->parent?$category->parent->name:'') . ' > ' . $category->name }}</td>
            <td>{{ config('sheba.front_url'). '/' .($category->getSlug() ?? '') }}</td>
            <td>{{ $category->catalog_thumb ?: $category->app_thumb}}</td>
            <td>new</td>
            <td>in stock</td>
            <td>{{ $category->catalog_price ?: ($category->start_price ?: 0) }} BDT</td>
            <td>Sheba.xyz</td>
            <td>{{ $category->ios_url }}</td>
            <td>{{ $category->ios_app_store_id }}</td>
            <td>{{ $category->ios_app_name }}</td>
            <td>{{ $category->android_url }}</td>
            <td>{{ $category->android_app_store_id }}</td>
            <td>{{ $category->android_app_name }}</td>
            <td>{{ $category->web_link }}</td>
            <td>{{ $category->web_deeplink }}</td>
            <td>{{ $category->sub_link }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
