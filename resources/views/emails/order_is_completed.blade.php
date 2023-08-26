<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Teek-it</title>
</head>
<body>

@if($userType == 'driver')
    <b>You have successfully completed your delivery.</b>
@else
    <b>You have successfully received your delivery.</b>
@endif
<br>
<br>
@if($userType != 'driver')
    <b>If you experienced any issues with your delivery, please contact us via email at:</b><br>
@else
    <b>If you experienced any issues whilst delivering, or were not able to complete the delivery,</b><br>
    <b>please contact us via email at:</b><br>
@endif
<b>admin@teekit.co.uk</b><br><br>
<img src="{{asset('teekit.png')}}" alt="">
</body>
</html>
