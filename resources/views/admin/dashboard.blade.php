@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1>Dashboard</h1>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">New Post</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Posts</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['published'] }}</div>
            <div class="stat-label">Published</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['drafts'] }}</div>
            <div class="stat-label">Drafts</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['videos'] }}</div>
            <div class="stat-label">Videos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['images'] }}</div>
            <div class="stat-label">Images</div>
        </div>
    </div>
@endsection
