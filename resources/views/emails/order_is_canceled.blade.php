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
<b>Hello {{$order->user->name}}</b><br><br>
<b>Your order from {{$order->store->name}} was unsuccessful.</b><br><br>
<p>Unfortunately {{$order->store->name}} were unable to complete your order. You have not been
    charged.</p><br>
<b>If you need any assistance, please contact us via email at:</b><br>
<b>admin@teekit.co.uk</b><br><br>
<img src="{{asset('teekit.png')}}" alt="">
</body>
</html>
