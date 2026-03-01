<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — URESIMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-[#f7f8fa]">

    <div class="w-full max-w-md px-6">

        {{-- Header — centered, slightly larger, formally emphasized --}}
        <div class="text-center mb-10">
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">URESIMS</h1>
            <p class="text-sm text-gray-500 mt-1.5">Extension Program Management</p>
            <p class="text-xs text-gray-400 mt-0.5">Carlos Hilado Memorial State University</p>
        </div>

        {{-- Card — clean, minimal, generous padding --}}
        <div class="bg-white rounded-xl border border-gray-200 p-8">

            <h2 class="text-base font-semibold text-gray-700 text-center mb-6">Sign in to your account</h2>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-600 mb-1.5">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400 outline-none transition"
                           placeholder="you@chmsu.edu.ph">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400 outline-none transition"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-gray-700 focus:ring-gray-400">
                        <span class="text-sm text-gray-500">Remember me</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-2.5 px-4 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg transition-colors duration-150">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">
            &copy; {{ date('Y') }} CHMSU — University Research and Extension Services
        </p>
    </div>

</body>
</html>
