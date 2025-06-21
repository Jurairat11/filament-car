<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    @vite('resources/css/app.css')
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="w-full max-w-md p-8 bg-white shadow rounded-xl dark:bg-gray-800">
        <h2 class="mb-6 text-2xl font-bold text-center text-gray-800 dark:text-white">
            Create your account
        </h2>

        @if ($errors->any())
            <div class="p-2 mb-4 text-sm text-red-600 bg-red-100 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="emp_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First
                    Name</label>
                <input id="emp_name" name="emp_name" type="text" required value="{{ old('emp_name') }}"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last
                    Name</label>
                <input id="last_name" name="last_name" type="text" required value="{{ old('last_name') }}"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="emp_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee
                    ID</label>
                <input id="emp_id" name="emp_id" type="text" required value="{{ old('emp_id') }}"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="dept_id"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                <select id="dept_id" name="dept_id" required
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Select Department</option>
                    @foreach (\App\Models\Department::all() as $dept)
                        <option value="{{ $dept->dept_id }}">{{ $dept->dept_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input id="password" name="password" type="password" required
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label for="password_confirmation"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Already have an
                    account?</a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded-md hover:bg-blue-700">
                    Register
                </button>
            </div>
        </form>
    </div>
</body>

</html>
