<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Group;
use App\Models\Organization;
use App\Notifications\GeneralNotification;
use App\Notifications\NewAnnouncement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller {
    // Return unread announcements for the authenticated user
    public function unreadAnnouncements(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // Get all announcements sent to this user that are not marked as read
        $unread = $user->unreadNotifications()->where('type', 'App\\Notifications\\GeneralNotification')->get();
        // Decode data payload for frontend
        $unread->transform(function ($n) {
            if (is_string($n->data)) {
                $n->data = json_decode($n->data, true);
            }
            return $n;
        });
        return response()->json(['unread' => $unread]);
    }

    // Adapter for frontend: GET /api/notifications (all)
    public function allNotifications(Request $request)
    {
        try {
            // Allow callers to request notifications for a specific notifiable.
            // If not provided, and a user is authenticated, default to that user.
            $notifiableType = $request->input('notifiable_type');
            $notifiableId = $request->input('notifiable_id');

            if (!$notifiableType || !$notifiableId) {
                $user = $request->user();
                if ($user) {
                    $notifiableType = 'App\\Models\\User';
                    $notifiableId = $user->id;
                } else {
                    // No filter provided and no authenticated user: avoid returning
                    // the entire notifications table; require a notifiable filter.
                    return response()->json(['error' => 'notifiable_type and notifiable_id required'], 400);
                }
            }

            $notifications =DB::table('notifications')
                ->where('notifiable_type', $notifiableType)
                ->where('notifiable_id', $notifiableId)
                ->orderByDesc('created_at')
                ->get();

            // Decode payloads
            $notifications->transform(function ($n) {
                if (isset($n->data) && is_string($n->data)) {
                    $n->data = json_decode($n->data, true);
                }
                return $n;
            });
            return response()->json(['notifications' => $notifications]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch notifications', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    // Adapter for frontend: GET /api/notifications/user (for authenticated user's notifications)
    public function userNotifications(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        try {
            $notifications = $user->notifications()->orderByDesc('created_at')->get();
            $notifications->transform(function ($n) {
                if (is_string($n->data)) {
                    $n->data = json_decode($n->data, true);
                }
                return $n;
            });
            return response()->json(['notifications' => $notifications]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user notifications', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch user notifications'], 500);
        }
    }
    // Return all announcements (for superadmin or testing)
    // Public: Return all announcements (no auth required)
    public function allAnnouncements()
    {
        $announcements = Announcement::orderByDesc('created_at')->get();
        return response()->json($announcements);
    }

    // Return a single announcement with related pivot data (organizations, groups, admins, members)

    public function showAnnouncement($id)
    {
        try {
            $announcement = Announcement::with(['organizations', 'groups', 'admins'])->select()->findOrFail($id);

            $data = [
                'id' => $announcement->id,
                'body' => $announcement->body,
                'sender_id' => $announcement->sender_id,
                'scheduled_at' => $announcement->scheduled_at,
                'sent_at' => $announcement->sent_at,
                'created_at' => $announcement->created_at,
                'updated_at' => $announcement->updated_at,
                'organizations' => $announcement->organizations->map(fn($org) => [
                    'id' => $org->id,
                    'name' => $org->org_name,
                    'name' => $org->organization_name,
                    'contact_email' => $org->user->email ?? null    ,
                    'user_id' => $org->user_id,
                    'user_first_name' => $org->user->first_name ?? null,
                    'user_last_name' => $org->user->last_name ?? null,

                ]),
                'groups' => $announcement->groups->map(fn($g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'organization_id' => $g->organization_id,
                    'organization_name' => $g->organization->org_name ?? null,
                    'organization_name' => $g->organization->organization_name ?? null,
                    'org_contact_email' => $g->organization->user->email ?? null,
                ]),
                'admins' => $announcement->admins->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->first_name.' '.$a->last_name,
                    'email' => $a->email,
                ]),
            ];

            $notifRows =DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->whereRaw("JSON_EXTRACT(data, '$.announcement_id') = ?", [$announcement->id])
                ->get();

            $readUserMap = [];
            foreach ($notifRows as $nr) {
                if (!empty($nr->read_at)) {
                    $readUserMap[$nr->notifiable_id] = true;
                }
            }

            $orgMap = [];
            foreach ($announcement->organizations as $o) {
                $orgMap[$o->id] = $o;
            }


            if (empty($orgMap)) {
                try {
                    $orgIds = $announcement->groups->pluck('organization_id')->filter()->unique()->values()->toArray();
                    if (!empty($orgIds)) {
                        $fetchedOrgs = Organization::whereIn('id', $orgIds)->get();
                        foreach ($fetchedOrgs as $fo) {
                            $orgMap[$fo->id] = $fo;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('[showAnnouncement] failed to fetch referenced organizations', ['announcement_id' => $announcement->id, 'error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'announcement' => $data,
                'notifications' => $notifRows,
            ]);
        } catch (\Exception $e) {
            Log::error('[showAnnouncement] error', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Announcement not found'], 404);
        }
    }

    // Send announcement to orgs, admins, groups
    public function send(Request $request)
    {
        $data = $request->validate([
            'body' => 'required|string',
            'organization_ids' => 'nullable|array',
            'admin_ids' => 'nullable|array',
            'group_ids' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ]);

        $senderId = $request->user() ? $request->user()->id : null;

        $announcement = Announcement::create([
            'body' => $data['body'],
            'sender_id' => $senderId,
            'scheduled_at' => $data['scheduled_at'] ? Carbon::parse($data['scheduled_at']) : null,
            'sent_at' => null,
            'dispatched_at' => now(),
        ]);

        if (!empty($data['organization_ids'])) {
            $announcement->organizations()->attach($data['organization_ids']);
        }
        if (!empty($data['admin_ids'])) {
            $announcement->admins()->attach($data['admin_ids']);
        }
        if (!empty($data['group_ids'])) {
            $announcement->groups()->attach($data['group_ids']);
        }

        $recipients = $this->getRecipientsForAnnouncement($announcement);
        $notification = new GeneralNotification($announcement);

        if ($announcement->scheduled_at) {
            $notification->delay($announcement->scheduled_at);
        }

        Notification::send($recipients, $notification);

        return response()->json(['success' => true, 'announcement' => $announcement]);
    }

    private function getRecipientsForAnnouncement(Announcement $announcement)
    {
        $announcement->load(['organizations.users', 'groups.members.user', 'admins']);

        $adminUsers = $announcement->admins;
        $orgUsers = $announcement->organizations->flatMap(function ($org) {
            return $org->users;
        });
        
        $users = $adminUsers->merge($orgUsers)->unique('id');
        
        $groupMembers = $announcement->groups->flatMap(function ($group) {
            return $group->members;
        });
        
        $userEmails = $users->pluck('email')->filter()->all();
        
        $membersOnly = $groupMembers->filter(function ($member) use ($userEmails) {
            if ($member->user_id) {
                return false;
            }
            return !in_array($member->email, $userEmails);
        })->unique('email');
        
        return $users->merge($membersOnly);
    }

    // Fetch announcements for a user
    public function userAnnouncements(Request $request)
    {
        $user = $request->user();
        // Get announcements related to user's orgs, groups, or admin status
        $announcements = Announcement::whereHas('admins', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->orWhereHas('organizations', function($q) use ($user) {
                $q->where('organizations.id', $user->organization_id);
            })
            ->orWhereHas('groups', function($q) use ($user) {
                $q->where('groups.id', $user->group_id);
            })
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['announcements' => $announcements]);
    }
    // Mark a notification as read for the authenticated user
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        return response()->json(['error' => 'Notification not found'], 404);
    }

    // Mark all notifications as read for the authenticated user
    public function markAllAsRead(Request $request)
{
    $user = $request->user();

    // Mark all unread notifications for this user as read
    $user->unreadNotifications->markAsRead();

    return response()->json([
        'message' => 'All notifications marked as read',
        // send back updated list
        'notifications' => $user->notifications
    ]);
}

    // Manual API endpoint to create a notification record (for testing)
    public function createNotification(Request $request)
    {
        $data = $request->validate([
            'notifiable_type' => 'required|string',
            'notifiable_id' => 'required|integer',
            'data' => 'required|array',
        ]);

        try {
            $payload = json_encode($data['data']);
            $id = (string) \Illuminate\Support\Str::uuid();
            DB::table('notifications')->insert([
                'id' => $id,
                'type' => 'App\\Notifications\\GeneralNotification',
                'notifiable_type' => $data['notifiable_type'],
                'notifiable_id' => $data['notifiable_id'],
                'data' => $payload,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('notifications')->where('id', $id)->first();
            return response()->json(['success' => true, 'notification' => $row]);
        } catch (\Exception $e) {
            Log::error('[createNotification] failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create notification'], 500);
        }
    }

}
