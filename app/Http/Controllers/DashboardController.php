<?php

namespace App\Http\Controllers;

use App\Helpers\LiveAgentApi;
use App\Http\Controllers\Controller;
use App\Models\CrmToken;
use App\Helpers\CRM;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function verifyUser($request)
    {
        $status = 4;

        $authToken = $request->input('auth_token')
            ?? $request->header('auth_token')
            ?? $request->header('X-Auth-Token')
            ?? null;
        if (!$authToken || empty($authToken)) {
            return [$status, null];
        }
        $token = CRM::decryptSSO($authToken);
        // dd($token);
        if (!$token) {
            return [$status, null];
        }
        $companyId = $token->companyId ?? null;
        $clearCache = $request->no_cache ?? false;
        $request->merge(['companyId' => $companyId]);
        if ($clearCache == 1) {
            list($keyFinal, $data)  =  $this->getMainCache();

            foreach ($data as $d) {
                Cache::forget($d);
            }
            Cache::forget($keyFinal);
        }
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
            $ticketStatus = $request->ticket_status ?? '';

            // Build status filter based on selection
            if ($ticketStatus === 'C') {
                // Only Open tickets
                $filters[] = ["status", "E", "C"];
            } elseif ($ticketStatus === 'L') {
                // Only Closed tickets
                $filters[] = ["status", "E", "L"];
            } else {
                // All (Both Open and Closed) - default behavior
                $filters[] = ["status", "IN", "C,L"];
            }
            // $filters[] = ["status", "IN", "L,C"];
            // $filters[] = ["status", "E", "L"];
            $filters[] = ["departmentid", "E", $departmentId];
            //date_resolved
            if ($resolvedStart) $filters[] = ["date_created", "D>=", $resolvedStart . " 00:00:00"];
            if ($resolvedEnd)  $filters[] = ["date_created", "D<=", $resolvedEnd . " 23:59:59"];
            $page = $request->page ?? 1;
            $length = $request->length ?? 10;

            $params = [];
            if (!empty($filters)) {
                $params['_filters'] = json_encode($filters);
            }

            if ($status == 1) {
                $allTickets = $this->fetchPaginatedData('tickets', $params, $length, $page, false);
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
        $filters[] = ["departmentid", "E", $departmentId];
        //$filters[] = ["status", "E", $status];
        if ($startDate)    $filters[] = ["date_created", "D>=", $startDate . " 00:00:00"];
        if ($endDate)      $filters[] = ["date_created", "D<=", $endDate . " 23:59:59"];
        // if ($resolvedStart) $filters[] = ["date_resolved", "D>=", $resolvedStart . " 00:00:00"];
        // if ($resolvedEnd)  $filters[] = ["date_resolved", "D<=", $resolvedEnd . " 23:59:59"];

        // 🔹 For DataTable AJAX
        if (!$request->ajax()) {
            // return;
        }

        $page = $request->page ?? 1;


        // 🔹 Fetch tickets (for status counts)
        $params = [];
        if (!empty($filters)) {
            $params['_filters'] = json_encode($filters);
        }
        if ($statusUser == 1) {
            $allTickets = $this->fetchPaginatedData('tickets', $params, 2000, $page, false);
        } else {
            $allTickets  = collect([]);
        }



        $statusCounts = collect($allTickets)->groupBy('status')->map->count();

        return response()->json([
            "status" => true,
            "records" => count($allTickets),
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
        // request()->authToken = $request->authToken ?? '';
        // list($statusUser, $departmentId)  = $this->verifyUser(request());
        list($statusUser, $departmentId)  = $this->verifyUser($request);
        try {
            $params = [
                '_sortDir'   => 'DESC',
                '_sortField' => 'date_created'
            ];
            $allMessages = [];
            // dd($statusUser);
            if ($statusUser == 1) {
                $allMessages = $this->fetchPaginatedData("tickets/{$ticketId}/messages", $params, 100);
            }

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

    public function getUserInfo($userId = null, Request $request)
    {
        try {
            // request()->authToken = $request->authToken ?? '';
            // list($statusUser, $departmentId)  = $this->verifyUser(request());

            list($statusUser, $departmentId)  = $this->verifyUser($request);
            $params = [];

            if (empty($userId)) {
                return;
            }
            $users = [];
            if ($statusUser == 1) {
                $users = $this->fetchPaginatedData("users/{$userId}", $params, 100);
            }

            // dD($allMessages);
            return response()->json([
                'success'  => true,
                'user' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'An error occurred while fetching user info : ' . $e->getMessage()
            ], 500);
        }
    }

    private function getMainCache()
    {
        $keyFinal = 'companyKeys.' . request()->get('companyId');
        // dd($keyFinal);
        $data = Cache::get($keyFinal, []);
        if (!is_array($data)) {
            $data = [$data];
        }

        return [$keyFinal, $data];
    }

    private function fetchPaginatedData(string $endpoint, array $params = [], int $perPage = 100, $page = 1, $all = true): array
    {
        $allData = [];


        do {
            $params['_page']      = $page;
            $params['_perPage']   = $perPage;
            $params['_sortDir']   = $params['_sortDir'] ?? 'DESC';
            $params['_sortField'] = $params['_sortField'] ?? 'date_created';
            $key = 'api.tickets.' . md5(json_encode([...$params, ...['url' => $endpoint]]));
            $response =  Cache::remember($key, 20 * 60, function () use ($params, $endpoint, $all, $allData, $key) {

                list($keyFinal, $data)  =  $this->getMainCache();
                $data[] = $key;
                Cache::put($keyFinal, $data);
                return LiveAgentApi::request('GET', $endpoint, $params);
            });

            if (!$response['success']) {
                break;
            }

            $data = $response['data'] ?? [];
            if (empty($data)) {
                break;
            }

            $allData = array_merge($allData, $data);
            if (!$all) {
                break;
            }
            $page++;
        } while (count($data) === $perPage);

        return $allData;
    }
}
