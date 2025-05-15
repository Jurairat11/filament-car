<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    @vite('resources/css/app.css')
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="w-full max-w-md p-8 bg-white shadow rounded-xl dark:bg-gray-800">
        <h2 class="mb-6 text-2xl font-bold text-center text-gray-800 dark:text-white">
            Sign in to your account
        </h2>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif


        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="emp_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee
                    ID</label>
                <input id="emp_id" name="emp_id" type="text" required autofocus value="{{ old('emp_id') }}"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div>
                <label for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input id="password" name="password" type="password" required
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div class="flex flex-col items-start gap-2 mt-4">
                <a href="{{ route('register') }}" class="text-sm text-blue-600 hover:underline">
                    Don't have an account?
                </a>

                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                    Forgot your password?
                </a>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-md hover:bg-blue-700">
                    Login
                </button>
            </div>
    </div>
    </form>
    </div>
</body>

</html>
