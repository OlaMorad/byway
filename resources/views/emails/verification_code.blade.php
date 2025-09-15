<!-- resources/views/emails/verification_code.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verification Code</title>
</head>
<body>
    <h2>مرحبا, {{ $user->first_name }}</h2>
    <p>كود التحقق الخاص بإيميلك هو :</p>
    <h1>{{ $user->verification_code }}</h1>
    {{-- <p>Please enter this code to verify your account.</p> --}}
</body>
</html>
