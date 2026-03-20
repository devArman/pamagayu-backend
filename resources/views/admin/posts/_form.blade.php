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

<div class="form-group">
    <label class="form-label" for="media">Media File {{ isset($post) ? '(leave empty to keep current)' : '' }}</label>
    <input class="form-input" type="file" id="media" name="media" accept="image/*,video/*" {{ isset($post) ? '' : 'required' }}>
    @error('media') <div class="form-error">{{ $message }}</div> @enderror

    @if(isset($post) && $post->media_path)
        <div style="margin-top: 0.5rem;">
            @if($post->isImage())
                <img src="{{ $post->media_url }}" alt="{{ $post->title }}" class="media-preview-lg">
            @else
                <video src="{{ $post->media_url }}" class="media-preview-lg" controls></video>
            @endif
        </div>
    @endif
</div>

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
