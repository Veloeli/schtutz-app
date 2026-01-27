<!DOCTYPE html>
<html data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Schtutz App</title>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link 
        rel="stylesheet" 
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    >
</head>

<body class="d-flex">

    <!-- Sidebar -->
    <nav class="bg-dark text-white p-3" style="width: 260px; min-height: 100vh;">
        <h4 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h4>

        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a href="/items" class="nav-link text-white">
                    <i class="bi bi-box-seam"></i> Items
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main content -->
    <main class="flex-grow-1 p-4">
        @yield('content')
    </main>

    <!-- Vite bundle -->
    @vite(['resources/js/app.js'])
</body>
</html>
