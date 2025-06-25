<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    @vite('resources/css/app.css')
</head>

<body class="flex items-center justify-center min-h-screen px-4 bg-gray-50 dark:bg-gray-900">
    <form method="POST" action="{{ route('password.email') }}"
        class="w-full max-w-md p-6 bg-white shadow rounded-xl dark:bg-gray-800">
        @csrf

        <h2 class="mb-6 text-xl font-bold text-center text-gray-800 dark:text-white">
            Forgot your password?
        </h2>

        <p class="mb-6 text-sm text-center text-gray-600 dark:text-gray-400">
            No problem. Just enter your email address and we'll send you a reset link.
        </p>

        @if (session('status'))
            <div class="mb-4 text-sm text-center text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-4">
            <label for="input_type" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                Email/Employee ID
            </label>
            <input type="text" name="input_type" id="input_type" required autofocus
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @error('email')
                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
            @enderror
            @error('emp_id')
                <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="w-full px-4 py-2 text-white transition bg-blue-600 rounded-md hover:bg-blue-700">
            Email Password Reset Link
        </button>

        <div class="mt-4 text-sm text-center">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                Back to login
            </a>
        </div>
    </form>
</body>

</html>
