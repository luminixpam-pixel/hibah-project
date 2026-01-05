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
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            padding: 35px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 320px;
        }

        /* LOGO */
       .login-card img {
            width: 280px;
            max-width: 100%;
            height: auto;
            display: block;      /* hilangkan space inline */
            margin: 0 auto;      /* tengah */
        }

        /* JUDUL & DESKRIPSI */
        .login-card h3 {
            margin: 2px 0 6px 0; /* RAPET */
            font-size: 18px;
        }
        .login-card p {
            margin: 0 0 14px 0;
            font-size: 15px;
        }

        /* INPUT */
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        /* BUTTON */
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
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #159e3e;
            transform: scale(1.05);
        }

        /* ERROR */
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* FORGOT PASSWORD */
        .forgot-password {
            text-align: right;
            margin-top: 6px;
        }

        .forgot-password a {
            color: #1fbd4c;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* MOBILE */
       @media (max-width: 480px) {
            .login-card img {
                width: 180px;
            }
        }

    </style>
</head>
<body>

    <div class="login-card">
        <img src="{{ asset('image/logo_v2.png') }}" alt="Logo E-Hibah">

        <h3>Selamat Datang di E-Hibah Universitas YARSI</h3>
        <p>Masukkan data untuk login ke akun E-Hibah YARSI</p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <input
                type="email"
                name="email"
                placeholder="Email"
                required
                value="{{ old('email') }}"
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <div class="forgot-password">
                <a href="{{ route('password.request') }}">Lupa password?</a>
            </div>

            <button type="submit" class="btn-login">
                Masuk
            </button>
        </form>
    </div>

</body>
</html>
