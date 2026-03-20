@extends('admin.layouts.app')

@section('title', 'Create Post')

@section('content')
    <div class="page-header">
        <h1>Create Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Back to Posts</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.posts._form')

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Create Post</button>
            </div>
        </form>
    </div>
@endsection
