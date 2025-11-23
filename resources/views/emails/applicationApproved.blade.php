<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Анкета одобрена</title>
</head>
<body>
<p>Уважаемый, {{ $fullName }}</p>

<p>Ваша анкета успешно прошла проверку.</p>
<p>Вы можете авторизоваться в личном кабинете:
    <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
</p>

<hr>
<p>С уважением, администрация сервиса Marriator</p>
</body>
</html>
