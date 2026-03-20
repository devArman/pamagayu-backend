@extends('admin.layouts.app')

@section('title', 'Posts')

@section('content')
    <div class="page-header">
        <h1>Posts</h1>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">New Post</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.posts.index') }}">
            <div class="filters">
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input class="form-input" type="text" name="search" value="{{ request('search') }}" placeholder="Search by title...">
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video</option>
                        <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Image</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Media</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Views</th>
                    <th>Order</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            @if($post->isImage())
                                <img src="{{ $post->media_url }}" alt="{{ $post->title }}" class="media-preview">
                            @elseif($post->thumbnail_url)
                                <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="media-preview">
                            @else
                                <div style="width:60px;height:60px;background:#e5e7eb;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;color:#6b7280;">Video</div>
                            @endif
                        </td>
                        <td>{{ Str::limit($post->title, 40) }}</td>
                        <td><span class="badge {{ $post->isVideo() ? 'badge-purple' : 'badge-blue' }}">{{ $post->type }}</span></td>
                        <td><span class="badge {{ $post->isPublished() ? 'badge-green' : 'badge-yellow' }}">{{ $post->status }}</span></td>
                        <td>{{ $post->is_featured ? 'Yes' : '-' }}</td>
                        <td>{{ number_format($post->views_count) }}</td>
                        <td>{{ $post->sort_order }}</td>
                        <td>{{ $post->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-primary btn-sm">Edit</a>

                                @if($post->isPublished())
                                    <form action="{{ route('admin.posts.unpublish', $post) }}" method="POST" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm">Unpublish</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.posts.publish', $post) }}" method="POST" class="inline-form">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Publish</button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline-form" onsubmit="return confirm('Delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6b7280; padding:2rem;">No posts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $posts->links() }}
        </div>
    </div>
@endsection
