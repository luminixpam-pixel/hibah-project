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
            width: 320px;
            background: rgba(255, 255, 255, 0.65);
            border-radius: 20px;
            overflow: hidden; /* PENTING: biar logo ngikut card */
            text-align: center;
        }

        .login-card img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
        }

        .card-body {
            padding: 8px 20px;
        }



        .btn-login {
            background-color: #1fbd4c;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 0;
            width: 100%;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: 0.2s ease;
            margin-top: 4px;
        }

        .btn-login:hover {
            background-color: #159e3e;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-card">
    <img src="{{ asset('image/logo_v2.png') }}" alt="Logo E-Hibah">
    <div class="card-body">
        <button class="btn-login" onclick="window.location.href='{{ route('login') }}'">
            Masuk
        </button>
    </div>
</div>

</body>
</html>
