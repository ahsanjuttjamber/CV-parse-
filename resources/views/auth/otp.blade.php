<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Verify OTP</h2>

    @if($errors->has('otp'))
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
            {{ $errors->first('otp') }}
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf
        <input type="text" name="otp" placeholder="Enter 6 digit OTP" maxlength="6" class="w-full mb-4 px-4 py-2 border rounded text-center tracking-widest" required autofocus>
        <button class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Verify OTP</button>
    </form>
</div>

<script>
document.querySelector('input[name="otp"]').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

</body>
</html>
