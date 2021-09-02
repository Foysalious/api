<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
<div class="container">
    <table>
        <tr>
            <td><h2><strong>Dear Sir/Mam,</strong></h2></td>
        </tr>
        <tr>
            <td><p>You have been invited/added to join as a co-worker. Start using <a target="_blank" href="https://business.sheba.xyz/">sBusiness.xyz</a> & digiGO by following these 2 steps:</p></td>
        </tr>
        <tr>
            <td>
                <p>
                    1. Download this app first here - <br>
                    For Android click here: <a target="_blank" href="https://play.google.com/store/apps/details?id=xyz.sheba.emanager&hl=en">Play store</a><br>
                    For iOS click here: <a target="_blank" href="https://www.apple.com/us/search/digigo-office?src=globalnav">App store</a><br><br>
                    @if(!empty($password))
                        2. Login with your account credentials- <br>
                        Email: <strong>{{ $email }}</strong> <br>
                        Password: <strong>{{ $password }}</strong> (Please change the password after login into your account) <br>
                    @else
                        2. Use Your existing password
                    @endif
                    <br><br>
                    We have the video tutorials ready for you to get started, check them out here: <a target="_blank" href="https://www.youtube.com/watch?v=i_w8iN2Q-uk&list=PLN1V1IQjMKZ0XiPJRUuhWlEFXtRCpl3r1">Tutorial </a>
                    <br><br>
                    <strong>Regards,<br>Team sBusiness.xyz</strong>
                </p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
