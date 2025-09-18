<?php

namespace App\Http\Controllers;

use App\Helpers\LiveAgentApi;
use App\Http\Controllers\Controller;
use App\Models\CrmToken;
use App\Helpers\CRM;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    public function verifyUser($request)
    {
        $status = 4;

        $authToken = $request->authToken ?? null;
        if (!$authToken || empty($authToken)) {
            return [$status, null];
        }
        $token = CRM::decryptSSO($authToken);

        if (!$token) {
            return [$status, null];
        }
        $companyId = $token->companyId ?? null;
        if (!$companyId) {
            return [$status, null];
        }
        $crmToken = CrmToken::where('company_id', $companyId)->first();
        if (!$crmToken) {
            return [2, null];
        }
        $user = $crmToken->user ?? null;
        if ($user->is_active == 0) {
            return [2, null];
        }
        $isConfigured = $user->is_configured;
        $assignedDepartment = $user->assignedDepartment;
        $departmentId = $assignedDepartment->department_id ?? null;
        if (!$crmToken || !$departmentId || $isConfigured == 0) {
            return [3, null];
        }
        return [1, $departmentId];
    }
    public function index(Request $request)
    {
        list($status, $departmentId)  = $this->verifyUser($request);

        if ($status == 2) {
            return view('user.inactive');
        }

        $departmentId =  null;
        $departmentName =  'Unknown';

        $statuses = $this->statuses();
        $today = $this->getDate(today()->copy()->startOfMonth());
        $endDateString = $this->getDate(today());
        return view('user.dashboard', [
            'departmentName' => $departmentName,
            'departmentId'   => $departmentId,
            'statuses'       => $statuses,
            'statusCounts'   => [],
            'filters'        => [
                'status'         => "",
                'start_date'     => $today,
                'end_date'       => $endDateString,
                'resolved_start' => $today,
                'resolved_end'   => $endDateString,
            ],
        ]);
    }

    private function statuses()
    {
        return [
            'I' => 'Init',
            'N' => 'New',
            'T' => 'Chatting',
            'P' => 'Calling',
            'R' => 'Resolved',
            'X' => 'Deleted',
            'B' => 'Spam',
            'A' => 'Answered',
            'C' => 'Open',
            'W' => 'Postponed',
            'L' => 'Closed'
        ];
    }

    private function channel_types()
    {
        return [
            'E' => 'email',
            'B' => 'contact button',
            'M' => 'contact form',
            'I' => 'invitation',
            'C' => 'call',
            'W' => 'call button',
            'F' => 'facebook',
            'A' => 'facebook message',
            'T' => 'twitter',
            'Q' => 'forum',
            'S' => 'suggestion*',
        ];
    }


    public function getDate($date)
    {
        return $date->format('Y-m-d');
    }
    public function getTickets(Request $request)
    {
        $filters = [];

        // 🔹 For DataTable AJAX
        if ($request->ajax()) {

            list($status, $departmentId)  = $this->verifyUser($request);
            $statuses = $this->statuses();

            $channel_types = $this->channel_types();
            $today = today();
            $startDateString = $this->getDate($today->copy()->startOfMonth());
            $resolvedStart = $request->resolved_start ?? $startDateString;
            $resolvedEnd   = $request->resolved_end ?? $this->getDate($today);
            $filters[] = ["status", "E", "L"];
            $filters[] = ["departmentid", "E", $departmentId];
            //date_resolved
            if ($resolvedStart) $filters[] = ["date_created", "D>=", $resolvedStart . " 00:00:00"];
            if ($resolvedEnd)  $filters[] = ["date_created", "D<=", $resolvedEnd . " 23:59:59"];
            $params = [];
            if (!empty($filters)) {
                $params['_filters'] = json_encode($filters);
            }

            if ($status == 1) {
                $allTickets = $this->fetchPaginatedData('tickets', $params, 100);
            } else {
                $allTickets  = collect([]);
            }

            return DataTables::of($allTickets)
                ->editColumn('status', function ($ticket) use ($statuses) {
                    return $statuses[$ticket['status']] ?? $ticket['status'];
                })
                ->editColumn('channel_type', function ($ticket) use ($channel_types) {
                    return $channel_types[$ticket['channel_type']] ?? $ticket['channel_type'];
                })
                ->addColumn('actions', function ($ticket) {
                    return '<button class="btn btn-sm btn-info view-messages" data-ticket-id="' . $ticket['id'] . '">Messages</button>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }
    public function dashboardStats(Request $request)
    {

        $today = today();


        list($statusUser, $departmentId)  = $this->verifyUser($request);
        $status        = $request->status ?? null;
        $startDate     = $request->start_date ?? $this->getDate($today->copy()->startOfMonth());
        $endDate       = $request->end_date ?? $this->getDate($today);
        $resolvedStart = $request->resolved_start ?? $today;
        $resolvedEnd   = $request->resolved_end ?? $today;

        $statuses = $this->statuses();
        // Build filters

        $filters = [];
        if ($departmentId) $filters[] = ["departmentid", "E", $departmentId];
        //$filters[] = ["status", "E", $status];
        if ($startDate)    $filters[] = ["date_created", "D>=", $startDate . " 00:00:00"];
        if ($endDate)      $filters[] = ["date_created", "D<=", $endDate . " 23:59:59"];
        // if ($resolvedStart) $filters[] = ["date_resolved", "D>=", $resolvedStart . " 00:00:00"];
        // if ($resolvedEnd)  $filters[] = ["date_resolved", "D<=", $resolvedEnd . " 23:59:59"];

        // 🔹 For DataTable AJAX
        if (!$request->ajax()) {
            // return;
        }


        // 🔹 Fetch tickets (for status counts)
        $params = [];
        if (!empty($filters)) {
            $params['_filters'] = json_encode($filters);
        }
        if ($statusUser == 1) {
            $allTickets = $this->fetchPaginatedData('tickets', $params, 2000);
        } else {
            $allTickets  = collect([]);
        }



        $statusCounts = collect($allTickets)->groupBy('status')->map->count();

        return response()->json([
            "status" => true,
            'activeStatus' => $statusUser,
            'departmentId'   => $departmentId,
            'statuses'       => $statuses,
            'statusCounts'   => $statusCounts,
            'filters'        => [
                'status'         => $status,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'resolved_start' => $resolvedStart,
                'resolved_end'   => $resolvedEnd,
            ]
        ]);
    }

    public function getMessages($ticketId, Request $request)
    {
        try {
            $params = [
                '_sortDir'   => 'DESC',
                '_sortField' => 'date_created'
            ];

            $allMessages = $this->fetchPaginatedData("tickets/{$ticketId}/messages", $params, 100);
            // dD($allMessages);
            return response()->json([
                'success'  => true,
                'messages' => $allMessages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'An error occurred while fetching messages: ' . $e->getMessage()
            ], 500);
        }
    }

    private function fetchPaginatedData(string $endpoint, array $params = [], int $perPage = 100): array
    {
        $allData = [];
        $page = 1;

        do {
            $params['_page']      = $page;
            $params['_perPage']   = $perPage;
            $params['_sortDir']   = $params['_sortDir'] ?? 'DESC';
            $params['_sortField'] = $params['_sortField'] ?? 'date_created';

            $response = LiveAgentApi::request('GET', $endpoint, $params);

            if (!$response['success']) {
                break;
            }

            $data = $response['data'] ?? [];
            if (empty($data)) {
                break;
            }

            $allData = array_merge($allData, $data);
            $page++;
        } while (count($data) === $perPage);

        return $allData;
    }
}
