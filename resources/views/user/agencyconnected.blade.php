@extends('custom-layouts.agency.app')

@section('content')

<div class="d-flex align-items-center justify-content-center vh-90">
    <div class="text-center">
        <div class="mb-4">
            <i class="bi bi-slash-circle display-1 text-danger"></i>
        </div>
        <h2 class="fw-bold text-dark">{{ get_default_settings('under_config_message') }}</h2>
        <p class="text-muted mb-4">
            {{ get_default_settings('contact_message') }}
        </p>
        You can access the portal via a custom menu link from agency sidebar with name "Agency Support Stats"
    </div>
</div>

@endsection
