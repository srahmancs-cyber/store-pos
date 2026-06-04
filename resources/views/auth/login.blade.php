<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex w-10 h-10 bg-gray-900 rounded-lg items-center justify-center mb-4">
                <span class="text-white text-sm font-bold">POS</span>
            </div>
            <h1 class="text-xl font-semibold text-gray-900">Sign in to your account</h1>
            <p class="text-sm text-gray-500 mt-1">{{ \App\Models\Setting::get('shop_name', config('app.name')) }}</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}"
                            class="form-input @error('email') border-red-400 @enderror"
                            placeholder="admin@store.com">
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="form-input @error('password') border-red-400 @enderror"
                            placeholder="••••••••">
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    @if($errors->has('general'))
                        <div class="alert alert-danger">
                            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                            {{ $errors->first('general') }}
                        </div>
                    @endif
                    <button type="submit" class="btn-primary w-full justify-center py-2.5">
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
