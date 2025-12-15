<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Hibah | Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: url('{{ asset('images/bg-campus.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 40px;
        }
        .login-card img {
            width: 100px;
            margin-bottom: 20px;
        }
        .btn-login {
            background-color: #1fbd4c;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 40px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .btn-login:hover {
            background-color: #159e3e;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('images/logo-ehibah.png') }}" alt="Logo E-Hibah">
        <br>

        {{-- INI HARUS KE LOGIN BUATAN KAMU, BUKAN FILAMENT --}}
        <button class="btn-login" onclick="window.location.href='{{ route('login') }}'">
            Masuk
        </button>
    </div>
</body>
</html>
