@extends('custom-layouts.admin.app')

@section('select_setting', 'active')
@section('content')

<div class="content-section">
    <div class="row">

        <!-- Alerts -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('setting.save') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>GoHighLevel Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="crm_client_id" class="form-label">Client ID</label>
                                <input type="text" class="form-control" id="crm_client_id" name="crm_client_id"
                                    placeholder="Enter GHL Client ID"
                                    value="{{ get_default_settings('crm_client_id') }}">
                            </div>
                            <div class="mb-3">
                                <label for="crm_client_secret" class="form-label">Secret ID</label>
                                <input type="password" class="form-control" id="crm_client_secret" name="crm_client_secret"
                                    placeholder="Enter GHL Secret ID"
                                    value="{{ get_default_settings('crm_client_secret') }}">
                            </div>
                            <div class="mb-3">
                                <label for="ghl_api_url" class="form-label">API URL</label>
                                <input type="url" class="form-control" id="ghl_api_url" name="ghl_api_url"
                                    placeholder="https://services.leadconnectorhq.com"
                                    value="{{ get_default_settings('ghl_api_url') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-headset me-2"></i>LiveAgent Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="la_api_url" class="form-label">LiveAgent URL</label>
                                <input type="url" class="form-control" id="la_api_url" name="la_api_url"
                                    placeholder="https://company.ladesk.com"
                                    value="{{ get_default_settings('la_api_url') }}">
                            </div>
                            <div class="mb-3">
                                <label for="la_api_key" class="form-label">API Key</label>
                                <input type="password" class="form-control" id="la_api_key" name="la_api_key"
                                    placeholder="Enter LiveAgent API Key"
                                    value="{{ get_default_settings('la_api_key') }}">
                            </div>
                            <div class="mb-3">
                                <label for="la_email" class="form-label">Agent Email</label>
                                <input type="email" class="form-control" id="la_email" name="la_email"
                                    placeholder="admin@company.com"
                                    value="{{ get_default_settings('la_email') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="form-group mt-3">
                <div class="row">
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary w-100 text-light px-4 mt-3 mb-0"
                           >Save</button>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('agency.index') }}"
                            class="btn btn-danger w-100 text-light px-4 mt-3 mb-0">Cancel</a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection
