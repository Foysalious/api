<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <style type="text/css">
        /* CLIENT-SPECIFIC STYLES */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }

        /* RESET STYLES */
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

        /* iOS BLUE LINKS */
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        /* MEDIA QUERIES */
        @media screen and (max-width: 600px) {
            .mobile-hide {
                display: none !important;
            }
            .mobile-center {
                text-align: center !important;
            }
            .mobile-table {
                width: 100% !important;
            }
            .text-center {
                text-align: center !important;
            }
        }

        /* ANDROID CENTER FIX */
        div[style*="margin: 16px 0;"] { margin: 0 !important; }
    </style>
</head>
<body style="margin: 0 !important; padding: 0 !important; background-color: #eeeeee;" bgcolor="#eeeeee">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="background-color: #eeeeee;" bgcolor="#eeeeee">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" class="mobile-table">
                @include('emails._header')
                <tr>
                    <td align="center" style=" padding: 60px;; background-color: #ffffff;" bgcolor="#ffffff">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;">
                            <tr>
                                <td align="center" style="font-family: Open Sans, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 400; line-height: 24px; padding-top: 0px;">
                                    <h2 style="font-size: 24px; font-weight: 600; line-height: 30px; color: #444; margin: 0;">
                                        Thanks for ordering from Sheba.xyz.
                                        <br>Your order ID is {{$order->code()}}.
                                    </h2>
                                    <h4>Login in to <a href="{{env('SHEBA_ACCOUNT_URL')}}/login?redirect_url={{env('SHEBA_FRONT_END_URL')}}" target="_blank">Sheba.xyz</a> for more details.</h4>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @include('emails._footer')

            </table>
        </td>
    </tr>
</table>
</body>
</html>