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
<b>Dear customer your order #{{$order->id}} is ready to be picked up.</b>
<br>
<b>please pick it from the store.</b>
<br>
<br>
<img src="{{asset('teekit.png')}}" alt="">
</body>
</html>
