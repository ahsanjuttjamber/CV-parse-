<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>

    @if($errors->any())
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="text" name="name" placeholder="Name" class="w-full mb-4 px-4 py-2 border rounded" required>
        <input type="email" name="email" placeholder="Email" class="w-full mb-4 px-4 py-2 border rounded" required>
        <input type="password" name="password" placeholder="Password" class="w-full mb-4 px-4 py-2 border rounded" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" class="w-full mb-4 px-4 py-2 border rounded" required>
        <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
    </form>
</div>
</body>
</html>
