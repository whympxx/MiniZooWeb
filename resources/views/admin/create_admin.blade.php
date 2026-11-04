
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.csrf')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @include('admin.partials.theme')
</head>
<body class="zoo-body zoo-admin min-h-screen flex items-center justify-center">
    @include('partials.scenery')
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="flex flex-col items-center px-6 pt-8 pb-4 bg-gradient-to-r from-blue-700 to-blue-500">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
                <span class="text-2xl font-bold text-white tracking-wide">Create Admin</span>
            </div>
            <p class="text-blue-100 text-sm">Create new administrator account</p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.admins.store') }}" class="px-8 py-6 space-y-5">
                @csrf
            @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">{{ $errors->first() }}</div>
        @endif

            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Enter username" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="Enter email" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="phone">Phone Number</label>
                <input id="phone" name="phone" type="tel" placeholder="e.g. 081234567890" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter password" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
                <p class="text-blue-600 text-xs mt-1">Must contain uppercase, lowercase, and number</p>
            </div>
            
            <div>
                <label class="block text-blue-900 text-sm font-medium mb-1" for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="password_confirmation" type="password" placeholder="Re-enter password" 
                       class="w-full px-4 py-2 rounded-lg bg-blue-100 text-blue-900 placeholder-blue-400 border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all duration-300" required />
            </div>
            
            <button type="submit" class="w-full py-2 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold text-lg shadow-md hover:from-blue-600 hover:to-blue-700 hover:shadow-lg transition-colors duration-200">
                Create Admin Account
            </button>
        </form>
        
        <div class="px-8 pb-6 text-center">
            <a href="{{ route('admin.dashboard') }}" class="text-blue-500 text-sm hover:underline">Back to Admin Dashboard</a>
        </div>
    </div>
</body>
</html> 