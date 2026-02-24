<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — URESIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-900 via-green-800 to-green-700">

    <div class="w-full max-w-md px-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                    <span class="text-2xl font-bold text-green-800">U</span>
                </div>
                <h1 class="text-xl font-bold text-gray-800">URESIMS</h1>
                <p class="text-sm text-gray-500 mt-1">Extension Program Management</p>
                <p class="text-xs text-gray-400 mt-0.5">Carlos Hilado Memorial State University</p>
            </div>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                           placeholder="Enter your email">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition-colors duration-150 shadow-sm">
                    Sign In
                </button>
            </form>

            <p class="text-center text-xs text-gray-400 mt-6">
                &copy; {{ date('Y') }} CHMSU — University Research and Extension Services Integration Management System
            </p>
        </div>
    </div>

</body>
</html>
