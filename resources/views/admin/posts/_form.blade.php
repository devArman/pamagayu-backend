<div class="form-group">
    <label class="form-label" for="type">Type</label>
    <select class="form-select" id="type" name="type" required>
        <option value="image" {{ old('type', $post->type ?? '') === 'image' ? 'selected' : '' }}>Image</option>
        <option value="video" {{ old('type', $post->type ?? '') === 'video' ? 'selected' : '' }}>Video</option>
    </select>
    @error('type') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="form-label" for="title">Title</label>
    <input class="form-input" type="text" id="title" name="title" value="{{ old('title', $post->title ?? '') }}" required>
    @error('title') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="form-label" for="description">Description</label>
    <textarea class="form-textarea" id="description" name="description">{{ old('description', $post->description ?? '') }}</textarea>
    @error('description') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group" id="media-group">
    <label class="form-label">
        Media File(s) {{ isset($post) ? '(leave empty to keep current)' : '' }}
    </label>

    <div id="media-video" style="display:none;">
        <input class="form-input" type="file" name="media" accept="video/*">
    </div>

    <div id="media-image" style="display:none;">
        <input class="form-input" type="file" name="media[]" accept="image/*,.heic,.heif" multiple>
        <small style="color: #6b7280; display: block; margin-top: 0.25rem;">You can select up to 6 images. HEIC (iPhone) files are supported and auto-converted.</small>
    </div>

    @error('media') <div class="form-error">{{ $message }}</div> @enderror
    @error('media.*') <div class="form-error">{{ $message }}</div> @enderror

    @if(isset($post))
        @if($post->isImage() && $post->images->isNotEmpty())
            <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                @foreach($post->images as $image)
                    <img src="{{ $image->image_url }}" alt="{{ $post->title }}" class="media-preview-lg" style="max-width: 150px;">
                @endforeach
            </div>
        @elseif($post->isImage() && $post->media_path)
            <div style="margin-top: 0.5rem;">
                <img src="{{ $post->media_url }}" alt="{{ $post->title }}" class="media-preview-lg">
            </div>
        @elseif($post->isVideo() && $post->media_path)
            <div style="margin-top: 0.5rem;">
                <video src="{{ $post->media_url }}" class="media-preview-lg" controls></video>
            </div>
        @endif
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const videoInput = document.getElementById('media-video');
    const imageInput = document.getElementById('media-image');

    function toggleMedia() {
        if (typeSelect.value === 'video') {
            videoInput.style.display = 'block';
            imageInput.style.display = 'none';
            imageInput.querySelector('input').value = '';
        } else {
            videoInput.style.display = 'none';
            imageInput.style.display = 'block';
            videoInput.querySelector('input').value = '';
        }
    }

    typeSelect.addEventListener('change', toggleMedia);
    toggleMedia();
});
</script>

<div class="form-group">
    <label class="form-label" for="thumbnail">Thumbnail (optional)</label>
    <input class="form-input" type="file" id="thumbnail" name="thumbnail" accept="image/*">
    @error('thumbnail') <div class="form-error">{{ $message }}</div> @enderror

    @if(isset($post) && $post->thumbnail_url)
        <div style="margin-top: 0.5rem;">
            <img src="{{ $post->thumbnail_url }}" alt="Thumbnail" class="media-preview-lg">
        </div>
    @endif
</div>

<div class="form-group">
    <label class="form-label" for="status">Status</label>
    <select class="form-select" id="status" name="status" required>
        <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
        <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
    </select>
    @error('status') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <label class="form-label" for="sort_order">Sort Order</label>
    <input class="form-input" type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $post->sort_order ?? 0) }}" min="0">
    @error('sort_order') <div class="form-error">{{ $message }}</div> @enderror
</div>

<div class="form-group">
    <div class="checkbox-group">
        <input type="hidden" name="is_featured" value="0">
        <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
        <label for="is_featured" class="form-label" style="margin-bottom:0;">Featured</label>
    </div>
    @error('is_featured') <div class="form-error">{{ $message }}</div> @enderror
</div>
