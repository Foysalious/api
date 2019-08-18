<!DOCTYPE html>
<html>
<head>
    <title>Profile Creation Invitation</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">
            <p>
                <span> Dear Employee,</span><br>
                You have been registered as an user in Sheba Business portal. <br>
                To login, go to <a href="{{env('SHEBA_BUSINESS_URL')}}">{{env('SHEBA_BUSINESS_URL')}}</a> then enter the
                following information : <br>
                Login ID : {{$email}}
                @if(!empty($password))
                    Password : <strong>{{$password}}</strong> <br>
                @else
                    Use Your existing password <br>
                @endif
                Please change your password and update your user user information after you login. <br>
                If you have technical problems, please contact admin department. <br>
                Regards <br>
                Admin <br>
            </p>
        </div>
    </div>
</div>
</body>
</html>
