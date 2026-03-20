<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; color: #1f2937; line-height: 1.5; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .nav { background: #1f2937; color: #fff; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between; height: 56px; }
        .nav-brand { font-size: 1.1rem; font-weight: 600; color: #fff; }
        .nav-links { display: flex; gap: 1.5rem; align-items: center; }
        .nav-links a { color: #d1d5db; font-size: 0.875rem; }
        .nav-links a:hover { color: #fff; text-decoration: none; }
        .nav-links a.active { color: #fff; font-weight: 500; }

        .container { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1.5rem; }

        .btn { display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; border: none; cursor: pointer; transition: all 0.15s; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; text-decoration: none; }
        .btn-success { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-warning { background: #d97706; color: #fff; }
        .btn-warning:hover { background: #b45309; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }

        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .form-textarea { min-height: 100px; resize: vertical; }
        .form-error { color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; }

        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem; }
        th { font-weight: 600; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
        tr:hover { background: #f9fafb; }

        .badge { display: inline-block; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: #1f2937; }
        .stat-label { font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem; }

        .filters { display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: end; }
        .filters .form-group { margin-bottom: 0; }

        .actions { display: flex; gap: 0.25rem; align-items: center; }
        .inline-form { display: inline; }

        .media-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
        .media-preview-lg { max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 6px; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h1 { font-size: 1.5rem; font-weight: 600; }

        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input { width: 16px; height: 16px; }

        .pagination { display: flex; gap: 0.25rem; justify-content: center; margin-top: 1rem; }
        .pagination a, .pagination span { padding: 0.375rem 0.75rem; border-radius: 4px; font-size: 0.875rem; }
        .pagination a { background: #fff; border: 1px solid #d1d5db; color: #374151; }
        .pagination a:hover { background: #f3f4f6; text-decoration: none; }
        .pagination .active span { background: #2563eb; color: #fff; border: 1px solid #2563eb; }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="{{ route('admin.dashboard') }}" class="nav-brand">{{ config('app.name') }} Admin</a>
        <div class="nav-links">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">Posts</a>
            <form action="{{ route('admin.logout') }}" method="POST" class="inline-form">
                @csrf
                <button type="submit" style="background:none; border:none; color:#d1d5db; cursor:pointer; font-size:0.875rem;">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
