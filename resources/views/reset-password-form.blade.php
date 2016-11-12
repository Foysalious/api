<!DOCTYPE html>
<html>
<head>
    <title>User Request</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if($show)
                    <form method="post">
                        {{csrf_field()}}
                        <label for="old_password">Old password</label>
                        <input type="password" id="old_password" type="password" name="old_password">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="password">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="password_confirmation">
                        <input type="submit" value="Change Password">
                    </form>
                @else
                    <p style="color: red;">Reset link has expired</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>