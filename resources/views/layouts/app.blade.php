<!DOCTYPE html>
<html data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no,date=no,address=no,email=no,url=no">

    <title>Schtutz App</title>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>
    <!-- HEADER -->
    <header id="app-header" class="bg-dark text-white sticky-top" style="z-index: 2000;">
        <div class="d-flex align-items-center justify-content-between py-2 px-3">
            <div class="fw-bold">Schtutz App</div>

            <!-- Mobile menu button -->
            <button 
                class="btn btn-outline-light d-md-none" 
                data-bs-toggle="offcanvas" 
                data-bs-target="#sidebar"
            >
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- ALERTS -->
        <div class="container pb-2">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm alert-responsive mb-1">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li class="alert-line">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm alert-responsive mb-1">
                    <span class="alert-line">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm alert-responsive mb-1">
                    <span class="alert-line">{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </header>

    <!-- LAYOUT WRAPPER -->
    <div class="d-flex">

        <!-- SIDEBAR -->
        <div class="offcanvas-md offcanvas-start bg-dark text-white"
             tabindex="-1"
             id="sidebar"
             style="width: 200px;">

            <div class="offcanvas-body p-3">

                <ul class="nav flex-column gap-1">
                    <li class="nav-item">
                        <a href="{{ route('balances.index') }}" 
                           class="nav-link {{ request()->is('balances*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-piggy-bank"></i> Balances
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('documents.index') }}" 
                           class="nav-link {{ request()->is('documents*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-cash-coin"></i> Documents
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('quotes.index') }}" 
                           class="nav-link {{ request()->is('quotes*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-graph-up"></i> Quotes
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('collections.index') }}" 
                           class="nav-link {{ request()->is('collections*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-card-checklist"></i> Collections
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('categories.index') }}" 
                           class="nav-link {{ request()->is('categories*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('securities.index') }}" 
                           class="nav-link {{ request()->is('securities*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-file-earmark-text"></i> Securities
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('rollups.index') }}" 
                           class="nav-link {{ request()->routeIs('rollups.*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-diagram-3"></i> Rollups
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('teams.index') }}" 
                           class="nav-link {{ request()->routeIs('teams.*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-people"></i> Teams
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('profile.edit') }}" 
                           class="nav-link {{ request()->routeIs('profile.*') ? 'active text-white fw-bold' : 'text-white' }}">
                            <i class="bi bi-person"></i> Profile
                        </a>
                    </li>

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="nav-link text-white bg-transparent border-0 w-100 text-start py-2">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>

            </div>
        </div>

        <!-- MAIN CONTENT -->
        <main class="flex-grow-1 p-4">
            @yield('content')
        </main>

    </div>

    @vite(['resources/js/app.js'])
    @yield('scripts')

    <style>
        @media (max-width: 768px) {
            #sidebar.offcanvas-start {
                top: 56px; /* match your header height */
                height: calc(100% - 56px);
            }
        }
    </style>
</body>
</html>

<!--
<script>
document.addEventListener("DOMContentLoaded", () => {
    const alerts = document.querySelectorAll('.alert-success');

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 500);
        }, 3000);
    });
});
</script>
-->