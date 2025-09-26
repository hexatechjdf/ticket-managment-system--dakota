@extends('custom-layouts.agency.app')

@section('content')
    <div class="content-section active" id="dashboard">
        <!-- Department Info -->
        <div class="row">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                Stats cache duration is set to <strong>20 minutes</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <div class="row ">
            <div class="col text-end">
                <a id="forceReload" class="btn btn-secondary btn-small force-cache mb-2 w-10">Force Refresh</a>
                <a id="hideStats" onclick="hideStats(this)" class="btn btn-primary btn-small  mb-2 w-10">Hide Stats</a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('dashboard') }}" class="row stats g-3 mb-4" id="filterForm">
            <div class="col-md-3">
                <label class="form-label">Start Date (Created)</label>
                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}"
                    id="startDate">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date (Created)</label>
                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}"
                    id="endDate">
            </div>

            <div class="col-md-3 d-none">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" id="statusSelect">
                    <option value="">All</option>
                    @foreach ($statuses as $key => $label)
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
            @foreach ($statuses as $key => $label)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card h-100 shadow-sm border-0">
                        <div class="card-body p-3 text-center">
                            <h6 class="card-subtitle mb-2 text-muted">{{ $label }}</h6>
                            <h2 class="card-title mb-0 text-primary stats-{{ $key }}">
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
                <h5 class="card-title mb-0">Tickets (Closed, Open)</h5>

                <!-- Filters on the right -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="ticketStatus" class="form-label mb-1">Status</label>
                        <select name="ticket_status" class="form-select" id="ticketStatusTable">
                            <option value="">All (Open & Closed)</option>
                            <option value="C">Open</option>
                            <option value="L">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="resolvedStart" class="form-label mb-1">Start Date (Created)</label>
                        <input type="date" name="resolved_start" class="form-control"
                            value="{{ $filters['resolved_start'] }}" id="resolvedStart">
                    </div>
                    <div class="col-md-4">
                        <label for="resolvedEnd" class="form-label mb-1">End Date (Created)</label>
                        <input type="date" name="resolved_end" class="form-control"
                            value="{{ $filters['resolved_end'] }}" id="resolvedEnd">
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive w-100">
                    <table id="ticketsTable" class="table table-striped table-bordered w-100 align-middle">
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Email</th>
                                <th>Status</th>
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

    <!-- Messages Modal - Redesigned to match LiveAgent -->
    <div class="modal fade" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen p-3">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messagesModalLabel">Ticket Conversation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="overflow: hidden;">
                    <div class="d-flex justify-content-center align-items-center py-5" id="loadingSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading conversation...</span>
                        </div>
                    </div>

                    <div id="messagesContainer" class="d-none ">
                        <div class="ticket-header p-4 border-bottom bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 id="ticketSubject" class="mb-0"></h5>
                                <span id="ticketStatus" class="badge bg-success"></span>
                            </div>
                            <div class="ticket-meta text-muted small">
                                <span id="ticketDate" class="me-3"></span>
                                <span id="ticketChannel"></span>
                            </div>
                        </div>
                        <div class="d-flex ">
                            <div class="conversation-container w-75" style="max-height: 70vh; overflow-y: auto;">
                                <div class="conversation-timeline p-3">
                                    <!-- Messages will be inserted here -->
                                </div>
                            </div>
                            <div class="ticket-container w-25 p-2 d-flex flex-column bg-light" style="">

                                <!-- <div class="ticket-u"></div> -->
                                <div class="ticket-s"></div>

                                <div class="ticket-z">
                                    <div class="fw-bold">Visitor Info</div>
                                </div>

                            </div>


                        </div>
                    </div>

                    <div id="noMessages" class="d-none text-center text-muted py-5">
                        <i class="bi bi-chat-dots fs-1"></i>
                        <p class="mt-2">No messages found for this ticket.</p>
                    </div>

                    <div id="errorContainer" class="d-none alert alert-danger m-4" role="alert">
                        <!-- Error message will be displayed here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --bs-btn-close-color: #fff;
        }

        /* LiveAgent-style message styling */
        .message-card {
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .message-header {
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-customer .message-header {
            background-color: #f0f7ff;
            border-bottom: 1px solid #d0e5ff;
        }

        .message-agent .message-header {
            background-color: #f9f9f9;
            border-bottom: 1px solid #e8e8e8;
        }

        .message-system .message-header {
            background-color: #fff4e6;
            border-bottom: 1px solid #ffe0b2;
        }

        .message-sender {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message-sender-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .message-time {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .message-event {
            overflow-wrap: break-word;
        }

        .message-content {
            padding: 16px;
            line-height: 1.5;
            box-shadow: unset;
            overflow-wrap: break-word;
        }



        .message-customer .message-content {
            background-color: #fafdff;
        }

        .message-agent .message-content {
            background-color: #ffffff;
        }

        .message-system .message-content {
            background-color: #fffdf6;
        }

        .internal-note {
            border-left: 3px solid #ff9800;
        }

        .ticket-container .system-event {
            text-align: left !important;
        }

        .ticket-container>div>div {
            margin: 16px 0;
            padding: 0 12px;
        }

        .message-attachments {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #e0e0e0;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            padding: 6px 0;
            color: #1976d2;
            text-decoration: none;
        }

        .attachment-item:hover {
            text-decoration: underline;
        }

        .attachment-icon {
            margin-right: 8px;
        }

        .system-event {
            text-align: center;
            margin: 16px 0;
            position: relative;
        }

        .system-event:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
            z-index: 1;
        }

        .system-event-text {
            display: inline-block;
            background: white;
            padding: 0 12px;
            position: relative;
            z-index: 2;
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
        integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@section('js-script')
    <script>
        function hideStats(el) {
            $('.stats').toggleClass('d-none');
            el.innerText = el.innerText == 'Hide Stats' ? "Show Stats" : "Hide Stats";
        }

        $(function() {
            let ssoToken = null;
            let currentTicketInfo = null;

            const messageHandler = ({
                data
            }) => {
                if (data.message === "REQUEST_USER_DATA_RESPONSE") {
                    window.removeEventListener("message", messageHandler);
                    ssoToken = data.payload;
                    initTable();
                    getDashboardStats();
                }
            };

            window.addEventListener("message", messageHandler);
            window.parent.postMessage({
                message: "REQUEST_USER_DATA"
            }, "*");

            // Initialize DataTable
            let table = null;

            let tickets = {};

            $(document).ready(function() {
                // Initialize ticket status filter
                $('#ticketStatusTable').on('change', function() {
                    table.ajax.reload();
                });
            });


            function getTickets(data, page = 1, callback) {
                const ticketStatus = $('#ticketStatusTable').val();
                const requestData = {
                    ...data,
                    status: $('#statusSelect').val(),
                    start_date: $('#startDate').val(),
                    end_date: $('#endDate').val(),
                    resolved_start: $('#resolvedStart').val(),
                    resolved_end: $('#resolvedEnd').val(),
                    _token: "{{ csrf_token() }}",
                    ticket_status: ticketStatus, // Add this line
                    auth_token: ssoToken,
                    no_cache: hardReload,
                    page: page
                };

                $.ajax({
                    url: "{{ route('dashboard.tickets') }}",
                    method: "POST",
                    data: requestData,
                    success: function(response) {
                        console.log('Server response:', response);
                        hardReload = 0;
                        // Pass the processed data back to DataTables
                        if (response.data.length > 0) {
                            setTimeout(getTickets, 400, data, (page + 1), null);
                        }

                        if (page == 1) {
                            callback(response);
                        } else {
                            table.rows.add(response.data).draw(false);
                        }

                    },
                    error: function(xhr, status, error) {
                        console.error('DataTable AJAX error:', error);
                    }
                });
            }

            function initTable() {
                table = $('#ticketsTable').DataTable({
                    processing: true,
                    serverSide: false,
                    pageLength: 15,
                    lengthChange: false,
                    ajax: function(data, callback, settings) {
                        // Debug the incoming DataTables parameters
                        console.log('DataTable request:', data);
                        getTickets(data, data.draw ?? 1, callback);
                        // Collect extra filter parameters

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
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'channel_type',
                            name: 'channel_type'
                        },
                        {
                            data: 'date_created',
                            name: 'date_created',
                            render: function(data, type, row) {
                                return formatDate(data);
                            }
                        },
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

                $('#ticketsTable').on('xhr.dt', function(e, settings, json, xhr) {

                    tickets = {};
                    json.data.forEach(x => {
                        tickets[x.id] = x;
                    })
                    console.log('Returned data:', json); // Full response data
                    // Do something with json.data if the data is under "data" key
                });
                table.on('length.dt', function(e, settings, len) {
                    console.log('Length changed to: ' + len);


                    table.ajax.reload();
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

            let currentStats = {};

            let hardReload = 0;

            function getDashboardStats(newStats = false, page = 1) {

                if (newStats) {
                    currentStats = {};
                }
                const url = "{{ route('dashboard.stats') }}";
                const data = {
                    status: document.querySelector('#statusSelect').value,
                    start_date: document.querySelector('#startDate').value,
                    end_date: document.querySelector('#endDate').value,
                    resolved_start: document.querySelector('#resolvedStart').value,
                    resolved_end: document.querySelector('#resolvedEnd').value,
                    auth_token: ssoToken,
                    no_cache: hardReload,
                    page
                };

                if (newStats) {
                    hardReload = 0;
                    for (let [k, v] of Object.entries(statuses)) {
                        document.querySelector('.stats-' + k).innerHTML = `{!! view('components.loader-spinner')->render() !!}`;
                    }
                }

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        },
                        body: JSON.stringify(data),
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(result => {
                        function triggerModal(obj) {
                            Swal.fire({
                                ...obj,
                                confirmButtonText: 'OK',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false
                            });
                            $('#dashboard').remove();
                        }

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
                            let currentCount = result.statusCounts[k] ?? 0;
                            let oldCount = currentStats[k] ?? 0;
                            let total = oldCount + currentCount;
                            currentStats[k] = total;
                        }

                        if (result.records > 0) {
                            setTimeout(getDashboardStats, 50, false, (page + 1));
                        } else {

                            for (let [k, v] of Object.entries(statuses)) {
                                document.querySelector('.stats-' + k).innerText = currentStats[k] ?? 0;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            }

            //getDashboardStats = debounce(getDashboardStats, 300);

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                getDashboardStats(true);
            });

            $('#statusSelect, #startDate, #endDate').on('change', getDashboardStats);

            $('#resolvedStart, #resolvedEnd, #ticketStatusTable').on('change', debounce(function() {
                table.ajax.reload();
            }, 400));

            // Clear modal content when it's hidden
            $('#messagesModal').on('hidden.bs.modal', function() {
                // Reset all content sections
                $('#loadingSpinner').removeClass('d-none');
                $('#messagesContainer').addClass('d-none');
                $('#noMessages').addClass('d-none');
                $('#errorContainer').addClass('d-none');

                // Clear the conversation content
                $('.conversation-timeline').empty();

                // Reset ticket info
                $('#ticketSubject').text('');
                $('#ticketStatus').text('');
                $('#ticketId').text('');
                $('#ticketDate').text('');
                $('#ticketChannel').text('');

                currentTicketInfo = null;
            });
            $(document).on('click', '.force-cache', function(e) {
                hardReload = 1;
                getDashboardStats(true);
                table.ajax.reload();
            })

            // Handle view messages button click
            $(document).on('click', '.view-messages', function() {
                const ticketId = $(this).data('ticket-id');
                const ticketSubject = $(this).data('ticket-subject');
                const ticketStatus = $(this).data('ticket-status');
                const ticketDate = $(this).data('ticket-date');
                const ticketChannel = $(this).data('ticket-channel');

                // Clear previous content first
                $('.conversation-timeline').empty();
                $('#loadingSpinner').removeClass('d-none');
                $('#messagesContainer').addClass('d-none');
                $('#noMessages').addClass('d-none');
                $('#errorContainer').addClass('d-none');
                currentTicketInfo = tickets[ticketId] ?? {};
                $('#messagesModalLabel').text(`Conversation for Ticket Code ${currentTicketInfo.code}`);
                loadMessages(ticketId);
                $('#messagesModal').modal('show');
            });

            // Function to load messages via AJAX
            function loadMessages(ticketId) {
                $('#loadingSpinner').removeClass('d-none');
                $('#messagesContainer').addClass('d-none');
                $('#noMessages').addClass('d-none');
                $('#errorContainer').addClass('d-none');

                // Update ticket header info
                if (currentTicketInfo) {
                    $('#ticketSubject').text(currentTicketInfo.subject);
                    $('#ticketStatus').text(currentTicketInfo.status);
                    $('#ticketDate').text(`Created: ${formatDate(currentTicketInfo.date_created)}`);
                    $('#ticketChannel').text(`Channel: ${currentTicketInfo.channel_type??""}`);
                }

                function hideSpinner() {
                    $('#loadingSpinner').addClass('d-none');
                }

                $.ajax({
                    url: `/tickets/${ticketId}/messages`,
                    method: 'GET',
                    headers: {
                        "X-Auth-Token": ssoToken
                    },
                    success: async function(response) {


                        if (response.success && response.messages && response.messages.length > 0) {

                            await renderMessages(response.messages);
                            $('#messagesContainer').removeClass('d-none');
                            hideSpinner()
                        } else {
                            $('#noMessages').removeClass('d-none');
                            hideSpinner()
                        }
                    },
                    error: function(xhr) {
                        hideSpinner()
                        $('#errorContainer')
                            .removeClass('d-none')
                            .text('Error loading messages: ' + (xhr.responseJSON?.error ||
                                'Unknown error'));
                    }
                });
            }


            async function fetchUserInfo(userId) {
                try {
                    if (userId == 'system00') {
                        return [];
                    }

                    const response = await fetch(`{{ route('tickets.user') }}/${userId}`, {
                        method: 'get',
                        headers: {
                            "X-Auth-Token": ssoToken
                        },
                    });
                    if (!response.ok) throw new Error(`Failed for ${userId}`);
                    const data = await response.json();
                    return data.user ?? [];
                } catch (err) {
                    console.error('Error fetching user info for:', userId, err);
                    return null;
                }
            }
            const userInfoMap = {};
            async function renderMessages(messageGroups) {
                const container = $('.conversation-timeline');

                const allMessages = [];

                const userIds = _.uniq(
                    _.flatMap(messageGroups, ticket =>
                        _.map(ticket.messages, msg => msg.userid)
                    )
                );

                const results = await Promise.all(userIds.map(id =>
                    fetchUserInfo(id).then(data => ({
                        id,
                        data
                    }))
                ));


                results.forEach(({
                    id,
                    data
                }) => {
                    if (data) userInfoMap[id] = data[0] ?? "";
                });

                console.log(userInfoMap);

                messageGroups.forEach(group => {
                    if (!group) return;

                    if (group.messages && Array.isArray(group.messages)) {
                        group.messages.forEach(message => {
                            if (message) {
                                allMessages.push({
                                    ...message,
                                    group_user_full_name: group.user_full_name,
                                    group_user_type: group.user_type,
                                    group_userid: group.userid,
                                    group_datecreated: group.datecreated
                                });
                            }
                        });
                    } else {
                        // This is a single message (not a group)
                        allMessages.push({
                            ...group,
                            group_user_full_name: group.user_full_name,
                            group_user_type: group.user_type,
                            group_userid: group.userid,
                            group_datecreated: group.datecreated
                        });
                    }
                });

                // Sort all messages by date
                // allMessages.sort((a, b) => new Date(a.datecreated) - new Date(b.datecreated));



                // Render each individual message
                allMessages.forEach(message => {
                    if (['E1'].includes(message.type) && !message.message) {
                        return;
                    }

                    // Determine message type for styling
                    let messageType = 'message-customer';
                    let senderName = 'Customer';
                    let avatarText = 'C';

                    // Use the most specific user information available
                    let userFullName =
                    ""; //message.group_user_full_name || message.user_full_name || "Unknown";
                    const userType = message.group_user_type || message.user_type;
                    const userId = message.userid; //message.group_userid ||
                    let ignoreData = false;
                    // Set sender name if available
                    if (userFullName) {
                        senderName = userFullName;

                    }

                    let user = userInfoMap[userId] ?? {};
                    let role = user.role ?? "";
                    userFullName = "Visitor";
                    if (role == 'agent') {
                        userFullName = user.name + `(Agent)`;
                    }
                    avatarText = userFullName.charAt(0).toUpperCase();
                    console.log(user, userId);
                    // Determine user type (customer, agent, or system)
                    if (['T', 'U', 'Z', 'I', 'S'].includes(message.type)) {


                        // System messages
                        let content = `
                        ${formatMessageContent(message)}`
                        ignoreData = true;
                        $('.ticket-container .ticket-' + message.type.toLowerCase()).append($(content));

                    } else if (userType === 'A' || userType === 'agent' ||
                        (userId && userId !== 'system00' && userId !== 'si6if3yp' && userId.length > 5)
                        ) {
                        // Agent messages
                        messageType = 'message-agent';
                        senderName = userFullName || 'Agent';

                    } else if (userId === 'system00') {
                        // System messages
                        messageType = 'message-system';
                        senderName = 'System';
                        avatarText = 'S';
                        ignoreData = true;
                    } else {
                        // Customer messages (default)
                        messageType = 'message-customer';
                        senderName = userFullName || 'Customer';

                        // Special case: if userid starts with 'si' it's likely a customer
                        if (userId && userId.startsWith('si')) {
                            senderName = userFullName || 'Customer';
                            avatarText = senderName.charAt(0).toUpperCase();
                        }
                    }

                    if (!ignoreData) {
                        // Create message card
                        const messageCard = $(`
                    <div class="message-card ${messageType}">
                        <div class="message-header">
                            <div class="message-sender " data-user-id="${userId}">
                                <span class="message-sender-avatar">${avatarText}</span>
                                <span class="message-sender-title">${senderName}</span>

                            </div>
                            <div class="message-time">${formatDate(message.datecreated)}</div>
                        </div>
                        <div class="message-content">
                            ${formatMessageContent(message)}
                        </div>
                    </div>
                `);

                        container.append(messageCard);
                    }

                });

                // Scroll to the bottom of the conversation
                $('.conversation-container').scrollTop($('.conversation-container')[0].scrollHeight);
            }

            // Helper function to format message content with null checking
            function formatMessageContent(message) {
                if (!message) return '<div class="message-text">No message content</div>';

                let content = '';
                const messageText = message.message || '';

                if (messageText.includes("User is not logged in")) {
                    return "";
                }
                // Check if the message contains HTML tags
                const hasHTML = /<[a-z][\s\S]*>/i.test(messageText);

                // Handle different message types
                if (message.type === 'I') {
                    // Internal note
                    content = `<div class="internal-note">
            <strong>Internal Note:</strong><br>
            ${hasHTML ? messageText : escapeHtml(messageText).replace(/\n/g, '<br>')}
        </div>`;
                } else if (message.type === 'F' || message.type === 'L') {
                    // File attachment handling (same as before)
                    let attachmentHtml = '';
                    try {
                        const attachmentData = JSON.parse(messageText);
                        if (Array.isArray(attachmentData)) {
                            attachmentData.forEach(item => {
                                if (Array.isArray(item) && item.length >= 2) {
                                    if (item[0] === 'name' && item[1]) {
                                        attachmentHtml += `
                                <a href="#" class="attachment-item" target="_blank">
                                    <span class="attachment-icon"><i class="bi bi-paperclip"></i></span>
                                    ${escapeHtml(item[1])}
                                </a>
                            `;
                                    }
                                }
                            });
                        }
                    } catch (e) {
                        attachmentHtml =
                            `<div class="message-text">${hasHTML ? messageText : escapeHtml(messageText).replace(/\n/g, '<br>')}</div>`;
                    }
                    content = `<div class="message-attachments">${attachmentHtml}</div>`;
                } else {
                    // For regular messages and other types
                    if (hasHTML) {
                        content = `<div class="message-text html-content">${sanitizeHTML(messageText)}</div>`;
                    } else {
                        content =
                            `<div class="message-text">${escapeHtml(messageText).replace(/\n/g, '<br>')}</div>`;
                    }
                }

                return content;
            }

            // HTML sanitization function
            function sanitizeHTML(html) {
                // Basic sanitization - remove script tags and other dangerous content
                return html
                    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                    .replace(/on\w+="[^"]*"/g, '')
                    .replace(/on\w+='[^']*'/g, '')
                    .replace(/javascript:/gi, '');
            }

            // Keep your existing escapeHtml function
            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) {
                    return map[m];
                });
            }

            // Helper function to format dates with null checking
            function formatDate(dateString) {
                if (!dateString) return 'N/A';

                try {

                    const readable = new Intl.DateTimeFormat('en-US', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    }).format(new Date(dateString.replace(' ', 'T')));
                    return readable;
                    // const date = new Date(dateString);
                    // return isNaN(date.getTime()) ? 'N/A' : date.toLocaleString();
                } catch (e) {
                    return 'N/A';
                }
            }
        });
    </script>
@endsection
