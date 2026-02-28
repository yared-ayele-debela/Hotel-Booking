@extends('admin.layouts.app')
@section('title', 'Manage Room Images')
@section('content')
<div class="container-fluid">
    <x-page-title title="Manage Images - {{ $room->name }}" :breadcrumbs="[['label' => 'Vendor', 'url' => route('admin.vendor.dashboard')], ['label' => 'Rooms', 'url' => route('admin.vendor.rooms.index')], ['label' => 'Images']]" />
    <x-alert />
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Images</h5>
                </div>
                <div class="card-body">
                    @if($images->count() > 0)
                        <div class="row" id="images-container">
                            @foreach($images as $image)
                            <div class="col-md-4 mb-3 image-item" data-id="{{ $image->id }}" data-sort="{{ $image->sort_order }}">
                                <div class="card">
                                    <img src="{{ $image->image_url }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $image->alt_text ?? $room->name }}">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">ID: {{ $image->id }}</small>
                                            @if($image->is_banner)
                                            <span class="badge bg-warning">Banner</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm" placeholder="Alt text" value="{{ $image->alt_text ?? '' }}" data-field="alt_text">
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-primary set-banner-btn" {{ $image->is_banner ? 'disabled' : '' }}>Set Banner</button>
                                            <button class="btn btn-sm btn-success save-alt-btn">Save</button>
                                            <button class="btn btn-sm btn-danger delete-image-btn">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-secondary" id="reorder-btn">Save Order</button>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No images uploaded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload New Images</h5>
                </div>
                <div class="card-body">
                    <form id="upload-form" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="images" class="form-label">Select Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/gif" required>
                            <div class="form-text">JPEG, PNG, GIF - Max 1MB each</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="banner_image" class="form-label">Banner Image</label>
                            <select class="form-select" id="banner_image" name="banner_image">
                                <option value="">No banner (first image will be banner)</option>
                                <option value="0">First uploaded image</option>
                                <option value="1">Second uploaded image</option>
                                <option value="2">Third uploaded image</option>
                                <option value="3">Fourth uploaded image</option>
                                <option value="4">Fifth uploaded image</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="upload-btn">
                            <span class="upload-text">Upload Images</span>
                            <span class="upload-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Uploading...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Instructions</h6>
                    <ul class="small">
                        <li>Upload multiple images at once</li>
                        <li>Drag and drop to reorder images</li>
                        <li>Set one image as banner (main display)</li>
                        <li>Add alt text for better SEO</li>
                        <li>Recommended size: 1200x800px</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.image-item {
    cursor: move;
}
.image-item.sortable-ghost {
    opacity: 0.4;
}
.image-item.sortable-drag {
    opacity: 0.9;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomId = {{ $room->id }};
    
    // Initialize sortable
    const container = document.getElementById('images-container');
    if (container) {
        new Sortable(container, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateSortOrders();
            }
        });
    }
    
    // Upload form
    document.getElementById('upload-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = document.getElementById('upload-btn');
        const text = btn.querySelector('.upload-text');
        const loading = btn.querySelector('.upload-loading');
        
        text.classList.add('d-none');
        loading.classList.remove('d-none');
        btn.disabled = true;
        
        fetch(`/admin/vendor/rooms/${roomId}/images`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Upload failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Upload failed: ' + error.message);
        })
        .finally(() => {
            text.classList.remove('d-none');
            loading.classList.add('d-none');
            btn.disabled = false;
        });
    });
    
    // Set banner
    document.querySelectorAll('.set-banner-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.closest('.image-item').dataset.id;
            
            fetch(`/admin/vendor/rooms/${roomId}/images/${imageId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_banner: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to set banner');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to set banner');
            });
        });
    });
    
    // Save alt text
    document.querySelectorAll('.save-alt-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = this.closest('.image-item');
            const imageId = item.dataset.id;
            const altText = item.querySelector('[data-field="alt_text"]').value;
            
            fetch(`/admin/vendor/rooms/${roomId}/images/${imageId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    alt_text: altText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Alt text saved');
                } else {
                    alert(data.message || 'Failed to save alt text');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save alt text');
            });
        });
    });
    
    // Delete image
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this image?')) return;
            
            const item = this.closest('.image-item');
            const imageId = item.dataset.id;
            
            fetch(`/admin/vendor/rooms/${roomId}/images/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                } else {
                    alert(data.message || 'Failed to delete image');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete image');
            });
        });
    });
    
    // Update sort orders
    function updateSortOrders() {
        const items = document.querySelectorAll('.image-item');
        const images = [];
        
        items.forEach((item, index) => {
            images.push({
                id: item.dataset.id,
                sort_order: index
            });
        });
        
        fetch(`/admin/vendor/rooms/${roomId}/images/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ images: images })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Failed to reorder images');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reorder images');
        });
    }
    
    // Save order button
    document.getElementById('reorder-btn')?.addEventListener('click', updateSortOrders);
});
</script>
@endsection
