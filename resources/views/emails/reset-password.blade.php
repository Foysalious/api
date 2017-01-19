<!DOCTYPE html>
<html>
<head>
    <title>User Request</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="title">
                <p>Please go to this link to reset your password:
                    http://localhost/sheba_new_api/public/reset-password/{{$customer->id}}/{{$code}}
                    This link will be valid for only 30 minutes.
                </p>
                <p style="color: red">If the above link is not clickable, try copying and pasting it into the address bar of your web browser.</p>
            </div>
        </div>
    </div>
</body>
</html>