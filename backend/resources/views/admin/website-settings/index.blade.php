@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Website Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">General Settings</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.website-settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="{{ $settings['site_name'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="site_email" name="site_email" value="{{ $settings['site_email'] }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3">{{ $settings['site_description'] }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="site_phone" name="site_phone" value="{{ $settings['site_phone'] }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="site_address" name="site_address" value="{{ $settings['site_address'] }}">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Brand Assets</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_logo" class="form-label">Site Logo</label>
                                <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                                @if($settings['site_logo'])
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" style="max-height: 60px;" class="img-thumbnail">
                                        <a href="{{ route('admin.website-settings.remove-logo') }}" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Are you sure you want to remove the logo?')">Remove</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_favicon" class="form-label">Favicon</label>
                                <input type="file" class="form-control" id="site_favicon" name="site_favicon" accept="image/*">
                                @if($settings['site_favicon'])
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Favicon" style="max-height: 32px;" class="img-thumbnail">
                                        <a href="{{ route('admin.website-settings.remove-favicon') }}" class="btn btn-sm btn-danger ms-2" onclick="return confirm('Are you sure you want to remove the favicon?')">Remove</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Social Media Links</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="social_facebook" class="form-label">Facebook URL</label>
                                <input type="url" class="form-control" id="social_facebook" name="social_facebook" value="{{ $settings['social_facebook'] }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="social_twitter" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control" id="social_twitter" name="social_twitter" value="{{ $settings['social_twitter'] }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="social_instagram" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control" id="social_instagram" name="social_instagram" value="{{ $settings['social_instagram'] }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="social_linkedin" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" id="social_linkedin" name="social_linkedin" value="{{ $settings['social_linkedin'] }}">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">SEO Settings</h5>
                    
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ $settings['meta_title'] }}">
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="2">{{ $settings['meta_description'] }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ $settings['meta_keywords'] }}">
                    </div>

                    <div class="mb-3">
                        <label for="google_analytics" class="form-label">Google Analytics Code</label>
                        <textarea class="form-control" id="google_analytics" name="google_analytics" rows="3" placeholder="Paste your Google Analytics tracking code here">{{ $settings['google_analytics'] }}</textarea>
                    </div>

                    <hr>

                    <h5 class="mb-3">Maintenance Mode</h5>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_mode">
                                Enable Maintenance Mode
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="maintenance_message" class="form-label">Maintenance Message</label>
                        <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="2">{{ $settings['maintenance_message'] }}</textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
