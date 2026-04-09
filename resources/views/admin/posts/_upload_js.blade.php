<script>
document.addEventListener('DOMContentLoaded', function() {
    const CHUNK_SIZE = 900 * 1024; // 900KB per chunk (under nginx default 1MB)
    const form = document.getElementById('post-form');
    const submitBtn = document.getElementById('submit-btn');
    const progressDiv = document.getElementById('upload-progress');
    const uploadError = document.getElementById('upload-error');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const uploadUrl = '{{ route("admin.posts.upload-chunk") }}';
    const redirectUrl = '{{ $redirectUrl }}';
    const btnText = '{{ $btnText }}';

    form.addEventListener('submit', function(e) {
        const typeSelect = document.getElementById('type');
        const mediaInput = typeSelect.value === 'video'
            ? document.querySelector('#media-video input[type="file"]')
            : document.querySelector('#media-image input[type="file"]');

        if (!mediaInput || !mediaInput.files.length) return; // no files, normal submit

        e.preventDefault();
        uploadError.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
        progressDiv.style.display = 'block';
        progressDiv.innerHTML = '';

        const files = Array.from(mediaInput.files);
        uploadAllFiles(files);
    });

    async function uploadAllFiles(files) {
        const uploadedPaths = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileLabel = files.length > 1 ? ' (' + (i+1) + '/' + files.length + ')' : '';
            showFileProgress(file.name + fileLabel, 0, file.size);

            try {
                const path = await uploadFileInChunks(file, i);
                uploadedPaths.push(path);
                showFileProgress(file.name + fileLabel, file.size, file.size, true);
            } catch (err) {
                showError('Upload failed for ' + file.name + ': ' + err.message);
                return;
            }
        }

        submitFormWithPaths(uploadedPaths);
    }

    function uploadFileInChunks(file, fileIndex) {
        return new Promise(async (resolve, reject) => {
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            const uploadId = 'upload_' + Date.now() + '_' + fileIndex + '_' + Math.random().toString(36).substr(2, 9);

            for (let i = 0; i < totalChunks; i++) {
                const start = i * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const chunk = file.slice(start, end);

                const formData = new FormData();
                formData.append('chunk', chunk, 'chunk');
                formData.append('chunk_index', i);
                formData.append('total_chunks', totalChunks);
                formData.append('upload_id', uploadId);
                formData.append('filename', file.name);
                formData.append('_token', csrfToken);

                try {
                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        let msg = 'HTTP ' + response.status;
                        try {
                            const data = await response.json();
                            msg = data.message || msg;
                        } catch(e) {}
                        reject(new Error(msg));
                        return;
                    }

                    const data = await response.json();
                    const uploaded = end;
                    showFileProgress(file.name, uploaded, file.size);

                    if (data.status === 'complete') {
                        resolve(data.path);
                        return;
                    }
                } catch (err) {
                    reject(new Error('Network error. Check connection.'));
                    return;
                }
            }
        });
    }

    function showFileProgress(name, loaded, total, done) {
        const pct = Math.round((loaded / total) * 100);
        const loadedMB = (loaded / 1024 / 1024).toFixed(1);
        const totalMB = (total / 1024 / 1024).toFixed(1);
        const color = done ? '#059669' : '#2563eb';

        progressDiv.innerHTML = '<div style="font-size:0.875rem;margin-bottom:0.25rem;">' +
            (done ? '&#10003; ' : '') + name + ' — ' + pct + '% (' + loadedMB + ' / ' + totalMB + ' MB)' +
            '</div>' +
            '<div style="background:#e5e7eb;border-radius:6px;height:8px;overflow:hidden;">' +
            '<div style="background:' + color + ';height:100%;width:' + pct + '%;transition:width 0.2s;"></div>' +
            '</div>';
    }

    function submitFormWithPaths(paths) {
        progressDiv.innerHTML = '<div style="font-size:0.875rem;color:#059669;">All files uploaded! Saving post...</div>';

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
                window.location.href = redirectUrl;
            } else {
                return response.text().then(function(text) {
                    showError('Failed to save post. Server returned ' + response.status);
                });
            }
        }).catch(function(err) {
            showError('Network error while saving. Files uploaded OK — try submitting again.');
        });
    }

    function showError(msg) {
        uploadError.textContent = msg;
        uploadError.style.display = 'block';
        progressDiv.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.textContent = btnText;
    }
});
</script>
