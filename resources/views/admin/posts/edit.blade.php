@extends('admin.layouts.app')

@section('title', 'Edit Post')

@section('content')
    <div class="page-header">
        <h1>Edit Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Back to Posts</a>
    </div>

    <div class="card">
        <form id="post-form" method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.posts._form')

            <div id="upload-progress" style="display:none; margin-top: 1rem;"></div>
            <div id="upload-error" class="alert alert-error" style="display:none; margin-top: 1rem;"></div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" id="submit-btn" class="btn btn-primary">Update Post</button>
            </div>
        </form>
    </div>

    @include('admin.posts._upload_js', ['redirectUrl' => route('admin.posts.index'), 'btnText' => 'Update Post'])
@endsection
