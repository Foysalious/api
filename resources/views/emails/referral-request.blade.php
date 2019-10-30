<!DOCTYPE html>
<html>
<head>
    <title>Business Request Invitation</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <style>

        body {
            font-family: 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;
            font-size: 18px;
        }

        .wrapper {
            width: 70%;
            margin: 0 auto;
        }

        .main-container {
            width: 100%;
            padding: 20px;
            background-color: #eee;
        }

        .sub-container {
            padding: 40px;
            background-color: #fff;
            margin: 0 auto;
            text-align: center;
        }

        .logo-container {
            text-align: center;
        }

        .logo {
            width: 300px;
            height: auto;
            margin: 0 0 20px 0;
        }

        .text-container {
            padding: 0 40px;
        }

        .main-text {
            font-weight: normal;
            margin-bottom: 0;
            line-height: 1.8em;
            text-align: center;
        }

        .highlight {
            color: #1b4280;
            font-weight: bold;
        }

        .button {
            font-size: 18px;
            padding: 14px 28px;
            background-color: #13b4d5;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        footer {
            padding: 40px 0;
            text-align: center;
            margin: 0 auto;
            color: #444;
        }

        footer ul {
            padding: 0;
            margin: 20px 0;
            list-style: none;
            display: inline-block;
        }

        footer ul li {
            height: 40px;
            width: 40px;
            float: left;
            padding: 0 10px;
        }

        footer ul li a img {
            width: 100%;
            height: auto;
        }

        .footer-copyright {
            font-size: 14px;
            color: #777777;
        }

    </style>

</head>
<body>
<div class="wrapper">
    <div class="main-container">
        <div class="sub-container">
            <div class="logo-container">
                <img class="logo" src="https://cdn-shebaxyz.s3.amazonaws.com/images/sheba_xyz/logo.png" alt="Sheba.xyz">
            </div>
            <div class="text-container">
                <p class="main-text">
                    Howdy, <br>
                    {{$profile->identity}} has sent you a gift! Please go to this link to register with Sheba & get <span
                            class="highlight">BDT 500</span> off on your first order.
                </p>
                <br>
                <a href="{{env('SHEBA_ACCOUNT_URL')}}/register?code={{$voucher->code}}"
                   class="button">
                    Register Now
                </a>
                <br>
                <br>
                <p style="color: red; font-size: 12px;">If the register button is not clickable, try copying and pasting this
                    {{env('SHEBA_ACCOUNT_URL')}}/register?code={{$voucher->code}} into the address bar of
                    your web browser.</p>
            </div>
        </div>

        <footer>
            <p>Find us on</p>
            <ul>
                <li>
                    <a href="https://www.facebook.com/sheba.xyz/">
                        <img src="https://cdn-shebaxyz.s3.amazonaws.com/icons/facebook.png" alt="facebook icon">
                    </a>
                </li>
                <li>
                    <a href="https://www.linkedin.com/company-beta/7582965/">
                        <img src="https://cdn-shebaxyz.s3.amazonaws.com/icons/youtube.png" alt="youtube icon">
                    </a>
                </li>
                <li>
                    <a href="https://www.youtube.com/channel/UCFknoAGYEBD0LqNQw1pd2Tg">
                        <img src="https://cdn-shebaxyz.s3.amazonaws.com/icons/linkedin.png" alt="linkedin icon">
                    </a>
                </li>
            </ul>
            <p class="footer-copyright">© Sheba.xyz - House-1218, Road-50, Avenue-11, Mirpur DOHS, Dhaka-1216.</p>
        </footer>
    </div>
</div>

</body>
</html>