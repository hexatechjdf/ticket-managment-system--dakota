@extends('custom-layouts.admin.app')

@section('content')

<div class="content-section active" id="dashboard">
    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <!-- Users Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stats-card users-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Total Users</h6>
                            <h2 class="card-title mb-0 text-primary">12,847</h2>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> +12% from last month
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stats-card tickets-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Open Tickets</h6>
                            <h2 class="card-title mb-0 text-warning">47</h2>
                            <small class="text-danger">
                                <i class="bi bi-arrow-up"></i> +3 new today
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-ticket-perforated-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stats-card revenue-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-muted">Total Revenue</h6>
                            <h2 class="card-title mb-0 text-success">$89,247</h2>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> +15% from last month
                            </small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart and Activities Row -->
    <div class="row">
        <!-- Chart Section -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Analytics Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-placeholder">
                        <div class="text-center">
                            <i class="bi bi-graph-up display-4 mb-3"></i>
                            <br>
                            Revenue Growth Chart
                            <br>
                            <small class="text-muted">Monthly performance metrics</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities Section -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">New ticket created</h6>
                                <p class="mb-1 text-muted small">Ticket #LA-2024-001 opened</p>
                                <span class="activity-time">2 minutes ago</span>
                            </div>
                            <i class="bi bi-ticket text-warning"></i>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">User registered</h6>
                                <p class="mb-1 text-muted small">John Doe joined the platform</p>
                                <span class="activity-time">15 minutes ago</span>
                            </div>
                            <i class="bi bi-person-plus text-success"></i>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">GHL sync completed</h6>
                                <p class="mb-1 text-muted small">Data synchronized successfully</p>
                                <span class="activity-time">1 hour ago</span>
                            </div>
                            <i class="bi bi-arrow-repeat text-primary"></i>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Payment received</h6>
                                <p class="mb-1 text-muted small">New subscription payment</p>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                            <i class="bi bi-currency-dollar text-info"></i>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">Ticket resolved</h6>
                                <p class="mb-1 text-muted small">Ticket #LA-2024-003 closed</p>
                                <span class="activity-time">3 hours ago</span>
                            </div>
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
