<!DOCTYPE html>
<html>
<head>
    <title>Trip Request Notifications</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
</head>
<body>
<p> Dear Sir,</p>
<p>{{$title}}</p>
<p>Name: {{$trip_requester}}</p>
<p>Pick Up Address: {{$trip_pickup_address}}</p>
<p>Drop Off Address: {{$trip_dropoff_address}}</p>
<p>Created At: {{$trip_request_created_at}}</p>
<p>Please follow this <a href="{{$link}}">this link</a> to take further actions.</p>
<p>Thanks for being with <a href="https://sbusiness.xyz">sBusiness.xyz</a></p>
</body>
</html>