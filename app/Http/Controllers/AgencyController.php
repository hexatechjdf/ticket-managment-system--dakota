<?php

namespace App\Http\Controllers;

use App\Helpers\CRM;
use App\Helpers\LiveAgentApi;
use App\Http\Controllers\Controller;
use App\Models\AssignedDepartment;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class AgencyController extends Controller
{

    public function index(Request $request)
    {
        $connecturl = CRM::directConnect();
        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'email', 'is_active', 'is_configured'])->where('role', 0);

            return DataTables::of($users)
                ->addIndexColumn()
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-danger">No</span>';
                })
                ->editColumn('assigned_department', function ($row) {
                    $department = $row->assignedDepartment ?? null;
                    if (!$department) {
                        return 'Not Assigned Yet';
                    }
                    return $department->name . ' - ' . $department->department_id;
                })


                ->editColumn('connected_agency', function ($row) {
                    $connected_agency = $row->token ?? null;
                    if (!$connected_agency) {
                        return 'Not Yet Connected';
                    }
                    $meta = $connected_agency->meta;
                    $meta = json_decode($meta);
                    return $meta->name . ' - ' . $meta->email;
                })

                ->editColumn('is_configured', function ($row) {
                    return $row->is_configured
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-danger">No</span>';
                })
                ->addColumn('action', function ($row) use ($connecturl) {
                    $editUrl = route('agency.edit', $row->id);
                    $deleteUrl = route('agency.destroy', $row->id);

                    $connecturlWithUserId = $connecturl . '&state=' . $row->id;

                    return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil-square"></i>
            </a>

              <a href="' . $connecturlWithUserId . '" class="btn btn-sm btn-info" target="_blank" title="Connect">
            <i class="bi bi-link-45deg"></i>
              </a>

            <form action="' . $deleteUrl . '" method="POST" style="display:inline-block;" class="delete-form">
                ' . csrf_field() . method_field('DELETE') . '
                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $row->id . '">
                    <i class="bi bi-trash"></i>
                </button>
            </form>';
                })
                ->rawColumns(['is_active', 'is_configured', 'action', 'assigned_department'])
                ->make(true);
        }

        return view('admin.agency.index');
    }

    public function create()
    {
        return view('admin.agency.manage')->with([
            'user' => null,
            'assignedDepartment' => null,
        ]);
    }
    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        // Fetch assigned department for this user
        $assignedDepartment = AssignedDepartment::where('user_id', $user->id)->first();

        return view('admin.agency.manage', compact('user', 'assignedDepartment'));
    }

    public function getAllDepartments()
    {
        $allDepartments = [];
        $page = 1;
        $perPage = 250;

        $assignedIds = AssignedDepartment::pluck('department_id')->map(function ($id) {
            return (string) $id;
        })->toArray();

        while (true) {
            $response = LiveAgentApi::request('GET', 'departments', [
                '_page'    => $page,
                '_perPage' => $perPage,
            ]);

            if (!$response['success'] || empty($response['data'])) {
                break;
            }

            $availableDepartments = array_filter($response['data'], function ($dept) use ($assignedIds) {
                return isset($dept['department_id']) && !in_array((string) $dept['department_id'], $assignedIds, true);
            });

            foreach ($availableDepartments as $dept) {
                $name  = (string) ($dept['name'] ?? 'Unknown');
                $depId = (string) ($dept['department_id'] ?? '');

                if ($depId) {
                    $allDepartments[] = [
                        'id'   => $name . '|' . $depId,
                        'text' => $name,
                    ];
                }
            }

            if (count($response['data']) < $perPage) {
                break;
            }
            $page++;
        }

        return response()->json($allDepartments);
    }


    /**
     * Handle both store and update in one function
     */
    public function save(Request $request, string $id = null)
    {
        $user = $id ? User::findOrFail($id) : new User();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($user->id ?? 'NULL'),
            'is_active' => 'required|boolean',
            'is_configured' => 'required|boolean',
            'department_id' => 'required',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->is_active = $request->is_active;
        $user->is_configured = $request->is_configured;

        if (!$id) {
            $user->password = Hash::make('Ticket$009');
        }

        $user->save();


        $userId = $user->id;

        $assignedDepartment = AssignedDepartment::where('user_id', $userId)->first();
        if (!$assignedDepartment) {
            $assignedDepartment = new AssignedDepartment();
        }

        list($name, $depId) = explode('|', $request->department_id);
        $assignedDepartment->name = $name;
        $assignedDepartment->department_id = $depId;
        $assignedDepartment->user_id = $userId;
        $assignedDepartment->save();
        if (!$assignedDepartment) {
            $user->is_configured = 0;
            $user->save();
        }
        $message = $id ? 'Agency updated successfully.' : 'Agency created successfully.';


        return redirect()->route('agency.index')->with('success', $message);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('agency.index')->with('success', 'Agency deleted successfully.');
    }
}
