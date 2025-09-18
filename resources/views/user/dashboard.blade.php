@extends('custom-layouts.agency.app')

@section('content')
<div class="content-section active" id="dashboard">

    <!-- Department Info -->

    <div class="row">
        <div class="col-md-12 text-right">
            <a id="hideStats" onclick="hideStats(this)" class="btn btn-primary mb-2 w-25">Hide Stats</a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('dashboard') }}" class="row stats g-3 mb-4" id="filterForm">
        <div class="col-md-3">
            <label class="form-label">Start Date (Created)</label>
            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}" id="startDate">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date (Created)</label>
            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}" id="endDate">
        </div>

        <div class="col-md-3 d-none">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" id="statusSelect">
                <option value="">All</option>
                @foreach($statuses as $key => $label)
                <option value="{{ $key }}" {{ $filters['status'] == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2 d-none">
            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            <button type="button" class="btn btn-outline-secondary" id="clearFilters">Clear</button>
        </div>
    </form>



    <!-- Status Cards -->
    <div class="row mb-4 stats">
        @foreach($statuses as $key => $label)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="card stats-card h-100 shadow-sm border-0">
                <div class="card-body p-3 text-center">
                    <h6 class="card-subtitle mb-2 text-muted">{{ $label }}</h6>
                    <h2 class="card-title mb-0 text-primary stats-{{$key}}">
                        @include('components.loader-spinner')
                    </h2>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tickets Table -->
    <div class="card tickets">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <!-- Title on the left -->
            <h5 class="card-title mb-0">Closed Tickets</h5>

            <!-- Filters on the right -->
            <div class="row g-3" style="max-width: 600px;">
                <div class="col-md-6">
                    <label for="resolvedStart" class="form-label mb-1">Start Date (Closed)</label>
                    <input
                        type="date"
                        name="resolved_start"
                        class="form-control"
                        value="{{ $filters['resolved_start'] }}"
                        id="resolvedStart">
                </div>
                <div class="col-md-6">
                    <label for="resolvedEnd" class="form-label mb-1">End Date (Closed)</label>
                    <input
                        type="date"
                        name="resolved_end"
                        class="form-control"
                        value="{{ $filters['resolved_end'] }}"
                        id="resolvedEnd">
                </div>
            </div>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table id="ticketsTable" class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>

                            <th>Owner</th>
                            <th>Email</th>

                            <th>Channel</th>
                            <th>Created</th>

                            <th>Subject</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Messages Modal -->
<div class="modal fade" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messagesModalLabel">Ticket Messages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-y: auto;">
                <div class="d-flex justify-content-center" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="messagesContainer" class="d-none">
                    <!-- Messages will be displayed here -->
                </div>
                <div id="noMessages" class="d-none text-center text-muted py-4">
                    No messages found for this ticket.
                </div>
                <div id="errorContainer" class="d-none alert alert-danger" role="alert">
                    <!-- Error message will be displayed here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
    function hideStats(el) {
        $('.stats').toggleClass('d-none');

        el.innerText = el.innerText == 'Hide Stats' ? "Show Stats" : "Hide Stats";
    }
    $(function() {
        let ssoToken = null;



        const messageHandler = ({
            data
        }) => {
            if (data.message === "REQUEST_USER_DATA_RESPONSE") {
                window.removeEventListener("message", messageHandler);
                ssoToken = data.payload;
                initTable()
                getDashboardStats();
            }
        };
        window.addEventListener("message", messageHandler);
        window.parent.postMessage({
            message: "REQUEST_USER_DATA"
        }, "*");
        // Initialize DataTable
        let table = null;

        function initTable() {
            table = $('#ticketsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('dashboard.tickets') }}",
                    method: "POST",
                    data: function(d) {
                        // Add filter parameters to the DataTable request
                        d.status = $('#statusSelect').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                        d.resolved_start = $('#resolvedStart').val();
                        d.resolved_end = $('#resolvedEnd').val();
                        d._token = "{{csrf_token()}}";
                        d.authToken = ssoToken;
                    }
                },
                columns: [{
                        data: 'owner_name',
                        name: 'owner_name'
                    },
                    {
                        data: 'owner_email',
                        name: 'owner_email'
                    },

                    {
                        data: 'channel_type',
                        name: 'channel_type'
                    },
                    {
                        data: 'date_created',
                        name: 'date_created'
                    },
                    // {
                    //     data: 'date_resolved',
                    //     name: 'date_resolved',
                    //     defaultContent: '-'
                    // },
                    {
                        data: 'subject',
                        name: 'subject'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        }

        // Clear filters functionality
        $('#clearFilters').on('click', function() {
            // Clear form fields
            $('#statusSelect').val('');
            $('#startDate').val('');
            $('#endDate').val('');
            $('#resolvedStart').val('');
            $('#resolvedEnd').val('');

            // Submit the form to reload the page with cleared filters
            $('#filterForm').submit();
        });

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        let statuses = @json($statuses);

        function getDashboardStats(newStats = false) {
            const url = "{{ route('dashboard.stats') }}";


            const data = {
                status: document.querySelector('#statusSelect').value,
                start_date: document.querySelector('#startDate').value,
                end_date: document.querySelector('#endDate').value,
                resolved_start: document.querySelector('#resolvedStart').value,
                resolved_end: document.querySelector('#resolvedEnd').value,
                authToken: ssoToken
            };

            if (newStats) {

                for (let [k, v] of Object.entries(statuses)) {
                    document.querySelector('.stats-' + k).innerHTML = `{!! view('components.loader-spinner')->render() !!}`;
                }

            }


            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // If using Laravel with CSRF token, add it here:
                        'X-CSRF-TOKEN': "{{csrf_token()}}",
                    },
                    body: JSON.stringify(data),
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(result => {
                    console.log('Data received:', result);
                    function triggerModal(obj) {
                        Swal.fire({
                            ...obj,
                            confirmButtonText: 'OK',
                            showConfirmButton: false,
                            allowOutsideClick: false, // Prevent close by clicking outside
                            allowEscapeKey: false, // Prevent close by ESC key
                            allowEnterKey: false // Optional: Prevent close by pressing Enter
                        });
                        $('#dashboard').remove();
                    }
                    //let statuses = result.statuses;

                    if ([2, 4].includes(result.activeStatus)) {
                        triggerModal({
                            icon: 'error',
                            title: 'Access Denied',
                            html: `<strong>{{ get_default_settings('inactive_message') }}</strong><br>{{ get_default_settings('contact_message') }}`

                        });
                        return;
                    } else if (result.activeStatus == 3) {
                        triggerModal({
                            icon: 'info',
                            title: 'Account under configuration',
                            html: `<strong>{{ get_default_settings('under_config_message') }}</strong><br>{{ get_default_settings('contact_message') }}`

                        });
                        return;
                    }

                    for (let [k, v] of Object.entries(statuses)) {
                        document.querySelector('.stats-' + k).innerText = result.statusCounts[k] ?? "0";
                    }

                    // Handle your response here (update UI or DataTable)
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        }
        getDashboardStats = debounce(getDashboardStats, 300);

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            getDashboardStats(true);
        });

        // Refresh data when filters change
        //

        $('#statusSelect, #startDate, #endDate').on('change', getDashboardStats);

        $('#resolvedStart, #resolvedEnd').on('change', debounce(function() {
            table.ajax.reload();
        }, 400));

        // Handle view messages button click
        $(document).on('click', '.view-messages', function() {
            const ticketId = $(this).data('ticket-id');
            $('#messagesModalLabel').text(`Messages for Ticket #${ticketId}`);
            loadMessages(ticketId);
            $('#messagesModal').modal('show');
        });

        // Function to load messages via AJAX
        function loadMessages(ticketId) {
            // Show loading, hide other sections
            $('#loadingSpinner').removeClass('d-none');
            $('#messagesContainer').addClass('d-none');
            $('#noMessages').addClass('d-none');
            $('#errorContainer').addClass('d-none');

            $.ajax({
                url: `/tickets/${ticketId}/messages?authToken=` + ssoToken,
                method: 'GET',
                success: function(response) {
                    $('#loadingSpinner').addClass('d-none');

                    if (response.success && response.messages && response.messages.length > 0) {
                        renderMessages(response.messages);
                        $('#messagesContainer').removeClass('d-none');
                    } else {
                        $('#noMessages').removeClass('d-none');
                    }
                },
                error: function(xhr) {
                    $('#loadingSpinner').addClass('d-none');
                    $('#errorContainer')
                        .removeClass('d-none')
                        .text('Error loading messages: ' + (xhr.responseJSON?.error || 'Unknown error'));
                }
            });
        }

        // Function to render messages in the modal
        function renderMessages(messageGroups) {
            const container = $('#messagesContainer');
            container.empty();

            messageGroups.forEach(group => {
                const groupElement = $('<div class="message-group mb-4"></div>');

                // Add group header
                const header = `
                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                    <span class="fw-bold">${group.user_full_name || 'System'}</span>
                    <small class="text-muted">${formatDate(group.datecreated)}</small>
                </div>
            `;
                groupElement.append(header);

                // Add messages in this group
                const messagesContainer = $('<div class="messages p-3"></div>');

                if (group.messages && group.messages.length > 0) {
                    group.messages.filter(t => ['I', 'M', 'S'].includes(t.type)).forEach(message => {
                        const messageClass = message.type === 'M' ? 'alert alert-info' : 'alert alert-secondary';
                        const messageElement = `
                        <div class="${messageClass} mb-2">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">${getMessageType(message.type)}</small>
                                <small class="text-muted">${formatDate(message.datecreated)}</small>
                            </div>
                            <div class="mt-2">${formatMessage(message)}</div>
                        </div>
                    `;
                        messagesContainer.append(messageElement);
                    });
                }

                groupElement.append(messagesContainer);
                container.append(groupElement);
            });
        }

        // Helper function to format dates
        function formatDate(dateString) {
            if (!dateString) return 'N/A';

            const date = new Date(dateString);
            return date.toLocaleString();
        }

        // Helper function to get message type label
        function getMessageType(type) {
            const types = {
                'M': 'Message',
                'Y': 'Legacy Message',
                'Q': 'Quoted Text',
                'I': 'Internal',
                'F': 'File',
                'T': 'Title',
                'E': 'End',
                'D': 'Disconnect',
                'H': 'Header',
                'R': 'Transfer',
                'S': 'System',
                'U': 'User Agent',
                'G': 'Tag',
                'V': 'Voice',
                '1': 'Voice Internal',
                'N': 'Note',
                'L': 'Note File',
                'Z': 'Form Field',
                'A': 'Text Header',
                'O': 'Text Footer',
                'J': 'Status',
                'B': 'Splitted',
                'W': 'Ranking Feature Reward',
                'P': 'Ranking Feature Punishment',
                'C': 'Ranking Feature Comment',
                'K': 'System Public',
                'X': 'System Visitor',
                '0': 'Error Footer',
                '2': 'Merged',
                '3': 'Invitation Reroute',
                '4': 'Undelivered Message',
                '5': 'Linked',
                '6': 'Removed Attachment'
            };
            return types[type] || type;
        }


        // Helper function to format message content
        function formatMessage(message) {
            if (message.format === 'H' || message.format === 'Y') {
                // HTML content
                return message.message;
            } else {
                // Plain text content
                return $('<div>').text(message.message).html().replace(/\n/g, '<br>');
            }
        }
    });
</script>
@endsection
