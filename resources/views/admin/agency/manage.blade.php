@extends('custom-layouts.admin.app')

@section('select_agency', 'active')
@section('content')
<div class="container-fluid py-4">

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Error Messages --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <i class="bi bi-person"></i>
            {{ $user ? 'Edit User' : 'Add User' }}
        </div>
        <div class="card-body">
            <form action="{{ $user ? route('agency.update', $user->id) : route('agency.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control"
                        value="{{ old('name', $user->name ?? '') }}" required>
                </div>



                <div class="mb-3">
                    <label class="form-label">Assign Department</label>
                    <select id="departmentSelect" name="department_id" class="form-control" style="width: 100%">
                        {{-- The pre-selected option will be added here by JavaScript if it exists --}}
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Is Active</label>
                    <select name="is_active" class="form-control" required>
                        <option value="0" {{ old('is_active', $user->is_active ?? '') == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('is_active', $user->is_active ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Is Configured</label>
                    <select name="is_configured" class="form-control" required>
                        <option value="0" {{ old('is_configured', $user->is_configured ?? '') == 0 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('is_configured', $user->is_configured ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    {{ $user ? 'Update' : 'Create' }}
                </button>
                <a href="{{ route('agency.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
    $(document).ready(function() {
        const $departmentSelect = $('#departmentSelect').select2({
            placeholder: "Loading departments...",
            allowClear: true
        });

        $.ajax({
            url: "{{route('agency.departments') }}",
            dataType: 'json',
            success: function(data) {
                data.forEach(function(department) {
                    let option = new Option(department.text, department.id, false, false);
                    $departmentSelect.append(option);
                });

                @if($assignedDepartment)
                let preselected = {
                    id: "{{ $assignedDepartment->name }}|{{ $assignedDepartment->department_id }}",
                    text: "{{ $assignedDepartment->name }}"
                };

                if (!$departmentSelect.find("option[value='" + preselected.id + "']").length) {
                    let option = new Option(preselected.text, preselected.id, true, true);
                    $departmentSelect.append(option);
                }
                $departmentSelect.val(preselected.id).trigger('change');
                @endif

                $departmentSelect.select2({
                    placeholder: "Search departments...",
                    allowClear: true
                });
            },
            error: function() {
                console.error('Failed to load departments.');
                $departmentSelect.select2({
                    placeholder: "Error loading departments",
                    allowClear: true
                });
            }
        });
    });
</script>
@endsection
