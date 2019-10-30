<!DOCTYPE html>
<html>
<head>
    <title>Job Report</title>
</head>
<body>
<table>
    <tr>
        <td>id</td>
        <td>title</td>
        <td>Description</td>
        <td>google_product_category</td>
        <td>link</td>
        <td>image_link</td>
        <td>condition</td>
        <td>availability</td>
        <td>price</td>
        <td>gtin</td>
        <td>brand</td>
        <td>mpn</td>
    </tr>

    @foreach($services as $service)
        <tr>
            <td>{{ $service->id }}</td>
            <td>{{ $service->name }}</td>
            <td>{{ $service->description}}</td>
            <td>{{ $service->category->parent->name.' > '.$service->category->name}}</td>
            <td>{{ env('SHEBA_FRONT_END_URL').'/service/'.$service->id.'/'.$service->slug }}</td>
            <td>{{ $service->thumb}}</td>
            <td>new</td>
            <td>in stock</td>
            <td>{{ isset($service->start_price)?$service->start_price:0}}</td>
            <td></td>
            <td>Sheba</td>
            <td></td>
        </tr>
    @endforeach
</table>
</body>
</html>