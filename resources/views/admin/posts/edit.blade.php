@extends('admin.layouts.app')

@section('title', 'Edit Post')

@section('content')
    <div class="page-header">
        <h1>Edit Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Back to Posts</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.posts._form')

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Update Post</button>
            </div>
        </form>
    </div>
@endsection
