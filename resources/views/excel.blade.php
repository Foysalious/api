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
        <td>link</td>
        <td>image_link</td>
        <td>price</td>
    </tr>

    @foreach($services as $service)
        <tr>
            <td>{{ $service->id }}</td>
            <td>{{ $service->name }}</td>
            <td>{{ $service->description}}</td>
            <td>{{ env('SHEBA_FRONT_END_URL').'/service/'.$service->id.'/'.$service->slug }}</td>
            <td>{{ $service->thumb}}</td>
            <td>{{ $service->start_price}}</td>
        </tr>
    @endforeach
</table>
</body>
</html>