<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{

    //Display a listing of the resource.
    //@param  \Illuminate\Http\Request  $request
    //@return \Illuminate\Http\JsonResponse

    public function index(Request $request): JsonResponse
    {
        $response_data = [];
        $status_code = 200;

        try {
            $user = $request->user();
            $query = Group::with('members');

            if ($user->hasRole('organizationadmin')) {
                $orgId = $this->resolveOrganizationId($user);
                if (!$orgId) {
                    $status_code = 404;
                    $response_data['error'] = 'Organization not found for user.';
                } else {
                    $query->where('organization_id', $orgId);
                    $response_data = $query->get();
                }
            } elseif ($user->hasRole('superadmin')) {
                $response_data = $query->get();
            } else {
                $status_code = 403;
                $response_data['error'] = 'Unauthorized.';
            }
        } catch (\Exception $e) {
            Log::error('Failed to retrieve groups.', ['error' => $e->getMessage()]);
            $status_code = 500;
            $response_data = ['error' => 'An unexpected error occurred.'];
        }

        return response()->json($response_data, $status_code);
    }


    //Store a newly created resource in storage.
    //@param  \App\Http\Requests\StoreGroupRequest  $request
    //@return \Illuminate\Http\JsonResponse

    public function store(StoreGroupRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $orgId = $this->resolveOrganizationId($user);
            if (!$orgId) {
                return response()->json(['error' => 'Could not resolve organization for the current user.'], 400);
            }

            $group = Group::create([
                'name' => $validated['name'],
                'organization_id' => $orgId,
                'user_id' => $user->id,
            ]);

            if (!empty($validated['member_ids'])) {
                $group->members()->sync($validated['member_ids']);
            }

            return response()->json($group->load('members'), 201);
        } catch (\Exception $e) {
            Log::error('Failed to create group.', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred while creating the group.'], 500);
        }
    }


    //Display the specified resource.
    //@param  \Illuminate\Http\Request  $request
    //@param  int  $id
    //@return \Illuminate\Http\JsonResponse

    public function show(Request $request, int $id): JsonResponse
    {
        $response_data = [];
        $status_code = 200;

        try {
            $user = $request->user();
            $query = Group::with('members');

            if ($user->hasRole('organizationadmin')) {
                $orgId = $this->resolveOrganizationId($user);
                $query->where('organization_id', $orgId);
            } elseif (!$user->hasRole('superadmin')) {
                $status_code = 403;
                $response_data['error'] = 'Unauthorized.';
            }

            if ($status_code === 200) {
                $group = $query->findOrFail($id);
                $response_data = [
                    'group' => $group,
                    'members' => $group->members()->with('memberRoles')->get()
                ];
            }
        } catch (ModelNotFoundException $e) {
            $status_code = 404;
            $response_data['error'] = 'Group not found or you do not have permission to view it.';
        } catch (\Exception $e) {
            Log::error('Failed to retrieve group.', ['group_id' => $id, 'error' => $e->getMessage()]);
            $status_code = 500;
            $response_data['error'] = 'An unexpected error occurred.';
        }

        return response()->json($response_data, $status_code);
    }


    //Resolve the organization ID for a given user.

    //@param  \App\Models\User  $user
    //@return int|null

    private function resolveOrganizationId(User $user): ?int
    {
        if ($user->organization_id) {
            return $user->organization_id;
        }

        $organization = Organization::where('user_id', $user->id)->first();

        return $organization?->id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  StoreGroupRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UpdateGroupRequest $request, int $id): JsonResponse
    {
        $response_data = [];
        $status_code = 200;
        try {
            $user = $request->user();
            $validated = $request->validated();

            $orgId = $this->resolveOrganizationId($user);
            if (!$orgId) {
                $status_code = 400;
                $response_data['error'] = 'Could not resolve organization for the current user.';
            }

            $group = Group::where('organization_id', $orgId)->findOrFail($id);
            
            $group->update([
                'name' => $validated['name'],
            ]);

            if (isset($validated['member_ids'])) {
                $group->members()->sync($validated['member_ids']);
            }

        $status_code = 200;
        $response_data = $group->load('members');
        } catch (ModelNotFoundException $e) {
            $status_code = 404;
            $response_data['error'] = 'Group not found.';
        } catch (\Exception $e) {
            Log::error('Failed to update group.', ['id' => $id, 'error' => $e->getMessage()]);
            $status_code = 500;
            $response_data['error'] = 'An unexpected error occurred while updating the group.';
        }

        return response()->json($response_data, $status_code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $response_data = [];
        $status_code = 200;
        try {
            $user = $request->user();
            if (!$user->hasRole('organizationadmin')) {
                $status_code = 403;
                $response_data['error'] = 'Unauthorized.';
            }

            $orgId = $this->resolveOrganizationId($user);
            if (!$orgId) {
                $status_code = 400;
                $response_data['error'] = 'Could not resolve organization for the current user.';
            }

            $group = Group::where('organization_id', $orgId)->findOrFail($id);
            $group->delete();

            $response_data['message'] = 'Group deleted successfully.';
        } catch (ModelNotFoundException $e) {
            $status_code = 404;
            $response_data['error'] = 'Group not found.';
        } catch (\Exception $e) {
            Log::error('Failed to delete group.', ['id' => $id, 'error' => $e->getMessage()]);
            $status_code = 500;
            $response_data['error'] = 'An unexpected error occurred while deleting the group.';
        }

        return response()->json($response_data, $status_code);
    }
}
