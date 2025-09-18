<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{

        //Display a listing of the resource for the organization admin.
        //@param  Request  $request
        //@return JsonResponse

    public function index(Request $request): JsonResponse
    {
        try {
            $orgId = $this->getOrganizationIdForCurrentUser($request->user());
            $members = Member::where('organization_id', $orgId)->with(['groups', 'memberRoles'])->get();

            return MemberResource::collection($members)->response();
        } catch (\Exception $e) {
            Log::error('Failed to retrieve members.', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


        //Store a newly created resource in storage.
        //@param  StoreMemberRequest  $request
        //@return JsonResponse

    public function store(StoreMemberRequest $request): JsonResponse
    {
        try {
            $orgId = $this->getOrganizationIdForCurrentUser($request->user());
            $validated = $request->validated();
            
            $memberData = array_merge($validated, [
                'organization_id' => $orgId,
                'user_id' => $request->user()->id,
            ]);

            $roleInput = $validated['member_role'] ?? null;
            $groupIds = $validated['group_ids'] ?? [];
            unset($memberData['member_role'], $memberData['group_ids']);
            
            $member = DB::transaction(function () use ($memberData, $groupIds, $roleInput) {
                $member = Member::create($memberData);

                if (!empty($groupIds)) {
                    $member->groups()->sync($groupIds);
                }
                if ($roleInput) {
                    $member->member_role = $roleInput;
                }
                return $member;
            });
            
            return (new MemberResource($member->load('groups', 'memberRoles')))
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            Log::error('Failed to create member.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred while creating the member.'], 500);
        }
    }


        //Display the specified resource.
        //@param  Request  $request
        //@param  int  $id
        //@return JsonResponse

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $orgId = $this->getOrganizationIdForCurrentUser($request->user());
            $member = Member::where('organization_id', $orgId)->with(['groups', 'memberRoles'])->findOrFail($id);
            
            return (new MemberResource($member))->response();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Member not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve member.', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


        //Update the specified resource in storage.
        //@param  UpdateMemberRequest  $request
        //@param  int  $id
        //@return JsonResponse

    public function update(UpdateMemberRequest $request, int $id): JsonResponse
    {
        try {
            $orgId = $this->getOrganizationIdForCurrentUser($request->user());
            $member = Member::where('organization_id', $orgId)->findOrFail($id);

            $validated = $request->validated();
            $roleInput = $validated['member_role'] ?? null;
            unset($validated['member_role']);
            
            DB::transaction(function () use ($member, $validated, $roleInput) {
                $member->update($validated);
                if ($roleInput !== null) {
                    $member->member_role = $roleInput;
                }
            });

            return (new MemberResource($member->load('memberRoles')))->response();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Member not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update member.', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred while updating the member.'], 500);
        }
    }


        //Remove the specified resource from storage.
        //@param  Request  $request
        //@param  int  $id
        //@return JsonResponse

    public function destroy(Request $request, int $id): JsonResponse
    {
        $response_data = [];
        $status_code = 200;
        try {
            $user = $request->user();
            if (!$user->hasRole('organizationadmin')) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            $orgId = $this->getOrganizationIdForCurrentUser($user);
            $member = Member::where('organization_id', $orgId)->findOrFail($id);
            $member->delete();

           $status_code = 204;
           $response_data = null;
        } catch (ModelNotFoundException $e) {
          $status_code = 404;
          $response_data['error'] = 'Member not found.';
        } catch (\Exception $e) {
            Log::error('Failed to delete member.', ['id' => $id, 'error' => $e->getMessage()]);
            $response_data['error'] = 'An unexpected error occurred while deleting the member.';
            $status_code = 500;
        }

        return response()->json($response_data, $status_code);
    }



        //Get the organization ID for the currently authenticated user.
        //@param  \App\Models\User  $user
        //@return int
        //@throws \Exception

    private function getOrganizationIdForCurrentUser(\App\Models\User $user): int
    {
        $orgId = $user->organization_id;

        if (!$orgId) {
            $organization = Organization::where('user_id', $user->id)->first();
            $orgId = $organization ? $organization->id : null;
        }

        if (!$orgId) {
            throw new \Exception('Organization not found for user.');
        }

        return $orgId;
    }
}
