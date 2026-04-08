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

            <div id="upload-progress" style="display:none; margin-top: 1rem;">
                <div style="font-size: 0.875rem; margin-bottom: 0.25rem;">
                    Uploading: <span id="upload-filename"></span> — <span id="upload-percent">0</span>%
                </div>
                <div style="background: #e5e7eb; border-radius: 6px; height: 8px; overflow: hidden;">
                    <div id="upload-bar" style="background: #2563eb; height: 100%; width: 0%; transition: width 0.2s;"></div>
                </div>
                <div id="upload-status" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;"></div>
            </div>

            <div id="upload-error" class="alert alert-error" style="display:none; margin-top: 1rem;"></div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" id="submit-btn" class="btn btn-primary">Update Post</button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('post-form');
        const submitBtn = document.getElementById('submit-btn');
        const progressDiv = document.getElementById('upload-progress');
        const progressBar = document.getElementById('upload-bar');
        const progressPercent = document.getElementById('upload-percent');
        const uploadFilename = document.getElementById('upload-filename');
        const uploadStatus = document.getElementById('upload-status');
        const uploadError = document.getElementById('upload-error');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        form.addEventListener('submit', function(e) {
            const typeSelect = document.getElementById('type');
            const mediaInput = typeSelect.value === 'video'
                ? document.querySelector('#media-video input[type="file"]')
                : document.querySelector('#media-image input[type="file"]');

            if (!mediaInput || !mediaInput.files.length) return;

            const totalSize = Array.from(mediaInput.files).reduce((sum, f) => sum + f.size, 0);
            if (totalSize < 5 * 1024 * 1024) return;

            e.preventDefault();
            uploadError.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';

            uploadFilesSequentially(mediaInput.files, 0, []);
        });

        function uploadFilesSequentially(files, index, uploadedPaths) {
            if (index >= files.length) {
                submitFormWithPaths(uploadedPaths);
                return;
            }

            const file = files[index];
            uploadFilename.textContent = file.name + ' (' + (index + 1) + '/' + files.length + ')';
            progressDiv.style.display = 'block';
            progressBar.style.width = '0%';
            progressPercent.textContent = '0';
            uploadStatus.textContent = 'Starting upload...';

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', csrfToken);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("admin.posts.upload") }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = pct + '%';
                    progressPercent.textContent = pct;
                    const mb = (e.loaded / 1024 / 1024).toFixed(1);
                    const totalMb = (e.total / 1024 / 1024).toFixed(1);
                    uploadStatus.textContent = mb + ' MB / ' + totalMb + ' MB';
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    uploadedPaths.push(data.path);
                    uploadFilesSequentially(files, index + 1, uploadedPaths);
                } else {
                    let msg = 'Upload failed (HTTP ' + xhr.status + ')';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                    showError(msg);
                }
            };

            xhr.onerror = function() {
                showError('Network error during upload. Please check your connection and try again.');
            };

            xhr.send(formData);
        }

        function submitFormWithPaths(paths) {
            uploadStatus.textContent = 'Saving post...';
            progressBar.style.width = '100%';

            const formData = new FormData(form);
            formData.delete('media');
            formData.delete('media[]');
            paths.forEach(function(path) {
                formData.append('uploaded_media[]', path);
            });

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'text/html',
                },
                body: formData,
            }).then(function(response) {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.href = '{{ route("admin.posts.index") }}';
                } else {
                    showError('Failed to save post. Please try again.');
                }
            }).catch(function() {
                showError('Network error while saving post.');
            });
        }

        function showError(msg) {
            uploadError.textContent = msg;
            uploadError.style.display = 'block';
            progressDiv.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Post';
        }
    });
    </script>
@endsection
