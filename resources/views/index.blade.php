<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <link rel="icon" href="/images/logo.png">
    <title>Сервис расчёта стоимости недвижимости</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons|Material+Icons+Outlined" rel="stylesheet" type="text/css">
    <!-- Map -->
</head>
<body>
<!-- Vue root -->
<div id="app"></div>

<!-- Add the following at the end of your body tag -->
<script src='{{ asset('js/main.js') }}'></script>
</body>
</html>
