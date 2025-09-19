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
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-gear me-2"></i>System Configuration Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- CRM Configuration -->
                                <div class="col-lg-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-gear me-2"></i>CRM Marketplace App Configuration
                                    </h6>
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
                                        <label for="distribution" class="form-label">Scope: (companies/readonly) - App Distribution: (Company) - App Type: (Private)</label>
                                    </div>
                                </div>

                                <!-- LiveAgent Configuration -->
                                <div class="col-lg-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-headset me-2"></i>LiveAgent Configuration
                                    </h6>
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
                                </div>
                            </div>

                            <div class="row">


                                <!-- Alert Messages -->
                                <div class="col-lg-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-chat-square-text me-2"></i>Alert Messages
                                    </h6>
                                    <div class="mb-3">
                                        <label for="contact_message" class="form-label">Contact Administration Message</label>
                                        <input type="text" class="form-control" id="contact_message" name="contact_message"
                                            placeholder="Contact Administration Message"
                                            value="{{ get_default_settings('contact_message') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="under_config_message" class="form-label">Under Configuration Message</label>
                                        <input type="text" class="form-control" id="under_config_message" name="under_config_message"
                                            placeholder="Enter Under Configuration Message"
                                            value="{{ get_default_settings('under_config_message') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="inactive_message" class="form-label">In Active Account Message</label>
                                        <input type="text" class="form-control" id="inactive_message" name="inactive_message"
                                            placeholder="Enter In Active Account Message"
                                            value="{{ get_default_settings('inactive_message') }}">
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-key me-2"></i>User Password Update
                                    </h6>
                                    <div class="mb-3">
                                        <label for="user_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="user_password" name="user_password"
                                            placeholder="Leave empty to remains the password unchanged"
                                            value="{{ old('user_password') }}">
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="form-group mt-3">
                <div class="row col-3 ms-auto">
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary w-100 text-light px-4 mt-3 mb-0">Save</button>
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
