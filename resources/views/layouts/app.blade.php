<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Hibah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #007bff;
        }
        .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar3"></i> E-Hibah
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    @stack('scripts')
</body>
</html>
