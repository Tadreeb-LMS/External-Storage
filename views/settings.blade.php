@extends('backend.layouts.app')

@section('title', 'External Storage Settings | ' . config('app.name'))

@push('after-styles')
<link rel="stylesheet" href="{{ url('modules/external-storage/public/css/external-storage.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-5">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-cloud mr-2"></i>
                            External Storage Settings
                        </h4>
                    </div>
                </div>
                <hr/>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <p class="text-muted">Configure your storage driver and S3-compatible storage credentials.</p>

                <form action="/external-apps/external-storage/settings" method="POST" id="storageSettingsForm">
                    @csrf

                    {{-- Storage Driver --}}
                    <div class="form-group row">
                        <label for="storage_driver" class="col-md-3 col-form-label">
                            Storage Driver <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-6">
                            <select name="storage_driver" id="storage_driver" class="form-control @error('storage_driver') is-invalid @enderror">
                                <option value="local" {{ ($settings['STORAGE_DRIVER'] ?? 'local') === 'local' ? 'selected' : '' }}>
                                    Local Storage
                                </option>
                                <option value="s3" {{ ($settings['STORAGE_DRIVER'] ?? '') === 's3' ? 'selected' : '' }}>
                                    S3 Compatible Storage
                                </option>
                            </select>
                            @error('storage_driver')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- S3 Configuration Fields --}}
                    <div class="s3-fields {{ ($settings['STORAGE_DRIVER'] ?? 'local') === 's3' ? 'active' : '' }}">
                        <hr/>
                        <h5 class="mb-3"><i class="fab fa-aws mr-1"></i> S3 Configuration</h5>

                        {{-- Access Key --}}
                        <div class="form-group row">
                            <label for="s3_access_key_id" class="col-md-3 col-form-label">
                                Access Key ID <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_access_key_id" id="s3_access_key_id"
                                       class="form-control @error('s3_access_key_id') is-invalid @enderror"
                                       value="{{ old('s3_access_key_id', $settings['S3_ACCESS_KEY_ID'] ?? '') }}"
                                       placeholder="Enter your access key ID">
                                @error('s3_access_key_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Secret Key --}}
                        <div class="form-group row">
                            <label for="s3_secret_access_key" class="col-md-3 col-form-label">
                                Secret Access Key <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="password" name="s3_secret_access_key" id="s3_secret_access_key"
                                       class="form-control @error('s3_secret_access_key') is-invalid @enderror"
                                       value="{{ old('s3_secret_access_key', $settings['S3_SECRET_ACCESS_KEY'] ?? '') }}"
                                       placeholder="Enter your secret access key">
                                @error('s3_secret_access_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Region --}}
                        <div class="form-group row">
                            <label for="s3_default_region" class="col-md-3 col-form-label">
                                Region <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_default_region" id="s3_default_region"
                                       class="form-control @error('s3_default_region') is-invalid @enderror"
                                       value="{{ old('s3_default_region', $settings['S3_DEFAULT_REGION'] ?? 'us-east-1') }}"
                                       placeholder="e.g. us-east-1">
                                @error('s3_default_region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Bucket --}}
                        <div class="form-group row">
                            <label for="s3_bucket" class="col-md-3 col-form-label">
                                Bucket Name <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_bucket" id="s3_bucket"
                                       class="form-control @error('s3_bucket') is-invalid @enderror"
                                       value="{{ old('s3_bucket', $settings['S3_BUCKET'] ?? '') }}"
                                       placeholder="e.g. my-bucket">
                                @error('s3_bucket')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Endpoint --}}
                        <div class="form-group row">
                            <label for="s3_endpoint" class="col-md-3 col-form-label">
                                Endpoint URL
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_endpoint" id="s3_endpoint"
                                       class="form-control @error('s3_endpoint') is-invalid @enderror"
                                       value="{{ old('s3_endpoint', $settings['S3_ENDPOINT'] ?? '') }}"
                                       placeholder="e.g. https://s3.example.com (optional)">
                                @error('s3_endpoint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Custom endpoint for S3-compatible services like MinIO or DigitalOcean Spaces. Leave empty for AWS S3.</small>
                            </div>
                        </div>

                        {{-- Custom URL --}}
                        <div class="form-group row">
                            <label for="s3_url" class="col-md-3 col-form-label">
                                Custom URL
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_url" id="s3_url"
                                       class="form-control @error('s3_url') is-invalid @enderror"
                                       value="{{ old('s3_url', $settings['S3_URL'] ?? '') }}"
                                       placeholder="e.g. https://cdn.example.com (optional)">
                                @error('s3_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Custom URL for accessing files (e.g., CloudFront CDN URL). Leave empty to use default S3 URL.</small>
                            </div>
                        </div>

                        {{-- Root Path --}}
                        <div class="form-group row">
                            <label for="s3_root" class="col-md-3 col-form-label">
                                Root Path
                            </label>
                            <div class="col-md-6">
                                <input type="text" name="s3_root" id="s3_root"
                                       class="form-control @error('s3_root') is-invalid @enderror"
                                       value="{{ old('s3_root', $settings['S3_ROOT'] ?? '') }}"
                                       placeholder="e.g. uploads (optional)">
                                @error('s3_root')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Root directory inside the bucket. All files will be stored under this path.</small>
                            </div>
                        </div>

                        {{-- Test Connection --}}
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-3">
                                <button type="button" id="testConnectionBtn" class="btn btn-outline-info">
                                    <i class="fas fa-plug mr-1"></i> Test Connection
                                </button>
                                <div id="testResult" class="test-result"></div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    {{-- Save Button --}}
                    <div class="form-group row">
                        <div class="col-md-6 offset-md-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script src="{{ url('modules/external-storage/public/js/external-storage.js') }}"></script>
@endpush
