<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Managment System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Select2 CSS (correct version) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

      <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --header-height: 70px;
            --transition: all 0.3s ease;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #495057;
            overflow-x: hidden;
        }

        /* Top Navigation */
        .top-navbar {
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            box-shadow: var(--box-shadow);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: var(--header-height);
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 2rem;
            display: flex;
            align-items: center;
        }

        .nav-brand i {
            font-size: 1.8rem;
            margin-right: 0.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            transition: var(--transition);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-section.active {
            display: block;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            font-weight: 600;
        }

        .card-header i {
            color: var(--primary);
            margin-right: 0.5rem;
        }

        .stats-card {
            transition: transform 0.2s ease-in-out;
            border-left: 4px solid var(--primary);
        }

        .stats-card.users-card {
            border-left-color: var(--primary);
        }

        .stats-card.tickets-card {
            border-left-color: var(--warning);
        }

        .stats-card.revenue-card {
            border-left-color: var(--success);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.9;
            color: var(--primary);
        }

        .users-card .stat-icon {
            color: var(--primary);
        }

        .tickets-card .stat-icon {
            color: var(--warning);
        }

        .revenue-card .stat-icon {
            color: var(--success);
        }

        /* Chart */
        .chart-placeholder {
            height: 300px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: 500;
        }

        /* Activity */
        .activity-item {
            border-left: 3px solid var(--primary);
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .activity-item:before {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            left: -7.5px;
            top: 5px;
        }

        .activity-time {
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Tables */
        .table-container {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .table thead th {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-color: #f1f3f6;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        /* Status badges */
        .ticket-status {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-open {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }

        .status-resolved {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }

        .status-closed {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }

        .ticket-priority {
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .priority-high {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }

        .priority-medium {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }

        .priority-low {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }

        /* Buttons */
        .btn {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.35rem 1rem;
            font-size: 0.875rem;
        }

        /* Forms */
        .form-control {
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Modal */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
        }

        .modal-header {
            background-color: var(--primary);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .history-item {
            border-left: 3px solid var(--primary);
            padding-left: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .history-item:before {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            left: -7.5px;
            top: 5px;
        }

        /* Chat widget */
        .chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            cursor: pointer;
            transition: var(--transition);
        }

        .chat-button:hover {
            transform: scale(1.1);
        }

        .chat-window {
            width: 350px;
            height: 450px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .message-content {
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 80%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .message.incoming .message-content {
            background-color: white;
            border: 1px solid #e0e0e0;
            align-self: flex-start;
        }

        .message.outgoing {
            align-items: flex-end;
        }

        .message.outgoing .message-content {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .message-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
            background: white;
        }

        .chat-input input {
            flex: 1;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .nav-brand span {
                display: none;
            }

            .nav-brand i {
                margin-right: 0;
                font-size: 1.5rem;
            }

            .nav-link span {
                display: none;
            }

            .nav-link i {
                margin-right: 0;
                font-size: 1.3rem;
            }

            .main-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .top-navbar {
                padding: 0.5rem 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .chat-window {
                width: 100%;
                height: 100%;
                position: fixed;
                top: 0;
                left: 0;
                border-radius: 0;
            }

            .stats-card .card-body {
                padding: 1.5rem;
            }

            .stat-icon {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            :root {
                --header-height: 60px;
            }

            .top-navbar {
                padding: 0.5rem;
            }

            .nav-link {
                padding: 0.4rem 0.7rem;
            }

            .user-info .d-none {
                display: none !important;
            }

            .main-content {
                padding: 1rem 0.5rem;
            }
        }
    </style>
</head>

<body>

    <!-- Top Navigation Bar -->

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- ✅ JS Section (correct order) -->

    <!-- jQuery (must be loaded first) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Bundle JS (needs jQuery for some plugins) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Select2 JS (must load after jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Page specific scripts -->
    @yield('js-script')

</body>
</html>

