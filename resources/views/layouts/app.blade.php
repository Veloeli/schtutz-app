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
    <div class="offcanvas-md offcanvas-start bg-dark text-white" 
         tabindex="-1" 
         id="sidebar" 
         style="width: 260px;">

        <div class="offcanvas-header d-md-none">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-3 d-md-block">

            <h4 class="mb-4 d-none d-md-block">
                <i class="bi bi-speedometer2"></i> Dashboard
            </h4>

            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a href="{{ route('items.index') }}" 
                       class="nav-link {{ request()->is('/') || request()->is('items*') ? 'active text-white fw-bold' : 'text-white' }}">
                        <i class="bi bi-box-seam"></i> Items
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('categories.index') }}" 
                       class="nav-link {{ request()->is('categories*') ? 'active text-white fw-bold' : 'text-white' }}">
                        <i class="bi bi-tags"></i> Categories
                    </a>
                </li>
            </ul>

        </div>
    </div>

    <!-- Main content -->
    <main class="flex-grow-1 p-4">
        <button 
            class="btn btn-dark d-md-none mb-3" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#sidebar"
        >
            <i class="bi bi-list"></i> Menu
        </button>

        @yield('content')
    </main>

    <!-- Vite bundle -->
    @vite(['resources/js/app.js'])
</body>
</html>
