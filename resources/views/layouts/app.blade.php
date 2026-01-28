<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Parser Admin</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-light">

    <!-- HEADER -->
    <header class="bg-white shadow-sm fixed-top w-100">
        <div class="container-fluid px-4 py-3 d-flex justify-content-end align-items-center">

            <!-- Admin Dashboard + User -->
            <div class="d-flex align-items-center gap-3">

                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                    Admin Dashboard
                </h5>

                <!-- Admin Name Button -->
                <button class="btn btn-outline-secondary d-flex align-items-center gap-2" data-bs-toggle="modal"
                    data-bs-target="#adminProfileModal">
                    <span>{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>

            </div>
        </div>
    </header>

    <!-- ADMIN PROFILE MODAL -->
    <div class="modal fade" id="adminProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-shield me-2 text-primary"></i>
                        Admin Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body text-center">

                    <img src="https://picsum.photos/seed/{{ Auth::user()->id }}/100/100"
                        class="rounded-circle mb-3 border" alt="Admin">

                    <h5 class="fw-bold">{{ Auth::user()->name }}</h5>
                    <p class="text-muted mb-1">{{ Auth::user()->email }}</p>

                    <span class="badge bg-primary mb-3">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>

                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Admin access verified
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer d-flex justify-content-between">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="container pt-5 mt-5">
        <!-- Menu Items -->
        <div class="py-1">
            <a href="{{ route('admin.dashboard') }}" class="block btn btn-primary px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="{{ route('admin.cvs.index') }}" class="block btn btn-success px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-file-alt mr-2"></i> Manage CVs
            </a>
        </div>
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>