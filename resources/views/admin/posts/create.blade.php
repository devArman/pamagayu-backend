@extends('admin.layouts.app')

@section('title', 'Create Post')

@section('content')
    <div class="page-header">
        <h1>Create Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Back to Posts</a>
    </div>

    <div class="card">
        <form id="post-form" method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.posts._form')

            <div id="upload-progress" style="display:none; margin-top: 1rem;"></div>
            <div id="upload-error" class="alert alert-error" style="display:none; margin-top: 1rem;"></div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" id="submit-btn" class="btn btn-primary">Create Post</button>
            </div>
        </form>
    </div>

    @include('admin.posts._upload_js', ['redirectUrl' => route('admin.posts.index'), 'btnText' => 'Create Post'])
@endsection
