@extends('custom-layouts.admin.app')

@section('select_agency', 'active')
@section('content')
<div class="container-fluid py-4">
    <div class="card">
            {{-- Success Message --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <br>
            @endif
        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <i class="bi bi-people"></i> Agencies
            </div>
            <a href="{{ route('agency.create') }}" class="btn btn-primary btn-sm">
                + Add
            </a>
        </div>
        <div class="table-container p-4">
            <table id="users-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Assigned Department</th>
                        <th>Connected Agency</th>
                        <th>Active</th>
                        <th>Configured</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection


@section('js-script')

<script>
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();

        let form = $(this).closest("form");

        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('agency.index') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },

                {
                    data: 'email',
                    name: 'email'
                },
                 {
                    data: 'assigned_department',
                    name: 'assigned_department'
                },
                 {
                    data: 'connected_agency',
                    name: 'connected_agency'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'is_configured',
                    name: 'is_configured',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    });
</script>
@endsection
