<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-6 text-center">Welcome, {{ Auth::user()->name }}</h2>

    <p class="text-center mb-4">You are logged in as <span class="font-semibold">{{ ucfirst(Auth::user()->role) }}</span>.</p>

    <p class="text-center mb-6">Email: {{ Auth::user()->email }}</p>

    @if(Auth::user()->role === 'admin')
        <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4 text-center">
            <p>⚠️ You have admin privileges but are viewing the user dashboard.</p>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">Go to Admin Dashboard</a>
        </div>
    @endif

    <form action="{{ route('logout') }}" method="POST" class="text-center">
        @csrf
        <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            Logout
        </button>
    </form>
</div>

</body>
</html>