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
            background: url('{{ asset('image/bg-campus.jpeg') }}') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 320px;
        }
        .login-card img {
            width: 90px;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
        .btn-login {
            background-color: #1fbd4c;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: #159e3e;
            transform: scale(1.05);
        }
        .error {
            color: red;
            font-size: 14px;
        }
        h6 {
         font-weight: normal;
        }

        .btn-login:hover {
            background-color: #159e3e;
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #1fbd4c;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('image/logo.png') }}" alt="Logo E-Hibah">
        <h3>Selamat Datang di E-Hibah Universitas YARSI</h3>
        <p style="font-size: 16px;">Masukkan data untuk login ke akun E-Hibah YARSI</p>



        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <input type="email" name="email" placeholder="Email" required value="{{ old('email') }}">
            <input type="password" name="password" placeholder="Password" required>
            <div class="forgot-password">
                <a href="{{ route('password.request') }}">Lupa password?</a>
            </div>
            <button class="btn-login" onclick="window.location.href='{{ route('login') }}'">
             Masuk
            </button>
            <form method="POST" action="{{ route('login.post') }}">




        </form>
    </div>
</body>
</html>
