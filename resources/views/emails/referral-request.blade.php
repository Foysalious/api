<!DOCTYPE html>
<html>
<head>
    <title>Business Request Invitation</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">
            <p>Please go to this link to register with Sheba & get 200 Tk on your first order
                off: {{env('SHEBA_ACCOUNT_URL').'/register?redirect_url='.env('SHEBA_FRONT_END_URL')."&referral_code=".$voucher->code}}
            </p>

            <p style="color: red">If the above link is not clickable, try copying and pasting it into the address bar of
                your web browser.</p>
        </div>
    </div>
</div>
</body>
</html>