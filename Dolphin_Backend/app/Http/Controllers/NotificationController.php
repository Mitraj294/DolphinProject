<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;

class NotificationController extends Controller {
    // Return unread announcements for the authenticated user
    public function unreadAnnouncements(Request $request)
    {
        $user = auth()->user();
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
                $user = auth()->user();
                if ($user) {
                    $notifiableType = 'App\\Models\\User';
                    $notifiableId = $user->id;
                } else {
                    // No filter provided and no authenticated user: avoid returning
                    // the entire notifications table; require a notifiable filter.
                    return response()->json(['error' => 'notifiable_type and notifiable_id required'], 400);
                }
            }

            $notifications = \DB::table('notifications')
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
            \Log::error('Failed to fetch notifications', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    // Adapter for frontend: GET /api/notifications/user (for authenticated user's notifications)
    public function userNotifications(Request $request)
    {
        $user = auth()->user();
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
            \Log::error('Failed to fetch user notifications', ['user_id' => $user->id, 'error' => $e->getMessage()]);
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
            $announcement = Announcement::with(['organizations', 'groups', 'admins'])->findOrFail($id);

            // For each attached group, attempt to load members (names and emails)
            $groupDetails = [];
            foreach ($announcement->groups as $g) {
                $members = [];
                if (method_exists($g, 'members')) {
                    try {
                        $members = $g->members()->get(['id', 'name', 'email'])->toArray();
                    } catch (\Exception $e) {
                        \Log::warning('[showAnnouncement] failed to load group members', ['group_id' => $g->id, 'error' => $e->getMessage()]);
                    }
                }
                $groupDetails[] = [
                    'id' => $g->id,
                    'name' => $g->name ?? null,
                    'members' => $members,
                ];
            }

            // Precompute notifications for this announcement to determine per-user read state
            $notifRows = \DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->whereRaw("JSON_EXTRACT(data, '$.announcement_id') = ?", [$announcement->id])
                ->get();

            $readUserIds = [];
            foreach ($notifRows as $nr) {
                if (!empty($nr->read_at)) {
                    $readUserIds[] = $nr->notifiable_id;
                }
            }
            $readUserIds = array_unique($readUserIds);

            $orgDetails = [];
            foreach ($announcement->organizations as $o) {
                // Determine if any user in this organization has read the announcement
                $orgUserIds = [];
                if (method_exists($o, 'users')) {
                    try {
                        $orgUserIds = $o->users()->pluck('users.id')->toArray();
                    } catch (\Exception $e) {
                        \Log::warning('[showAnnouncement] failed to pluck org users', ['org_id' => $o->id, 'error' => $e->getMessage()]);
                    }
                }
                $isRead = false;
                if (!empty($orgUserIds) && !empty($readUserIds)) {
                    if (count(array_intersect($orgUserIds, $readUserIds)) > 0) {
                        $isRead = true;
                    }
                }
                $orgDetails[] = [
                    'id' => $o->id,
                    'name' => $o->org_name ?? $o->name ?? null,
                    'read' => $isRead,
                ];
            }

            $adminDetails = [];
            foreach ($announcement->admins as $a) {
                $adminDetails[] = [
                    'id' => $a->id,
                    'name' => $a->name ?? $a->full_name ?? null,
                    'email' => $a->email ?? null,
                ];
            }

            return response()->json([
                'announcement' => $announcement,
                'groups' => $groupDetails,
                'organizations' => $orgDetails,
                'admins' => $adminDetails,
            ]);
        } catch (\Exception $e) {
            \Log::error('[showAnnouncement] error', ['id' => $id, 'error' => $e->getMessage()]);
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
        \Log::info('[AnnouncementController@send] scheduled_at received', ['scheduled_at' => $data['scheduled_at'] ?? null]);
        $scheduledAtRaw = $data['scheduled_at'] ?? null;
        if ($scheduledAtRaw) {
            // Try to parse with Carbon::parse, fallback to strict format if needed
            try {
                // Remove trailing Z or timezone if present
                $scheduledAtRaw = preg_replace('/[TZ]|(\+\d{2}:\d{2})$/', '', $scheduledAtRaw);
                $scheduledAtUtc = Carbon::parse(trim($scheduledAtRaw))->setTimezone('UTC');
            } catch (\Exception $e) {
                \Log::error('Failed to parse scheduled_at', ['input' => $scheduledAtRaw, 'error' => $e->getMessage()]);
                $scheduledAtUtc = null;
            }
        } else {
            $scheduledAtUtc = null;
        }
        $announcement = Announcement::create([
            'body' => $data['body'],
            'sender_id' => auth()->id(),
            'scheduled_at' => $scheduledAtUtc,
            'sent_at' => isset($data['scheduled_at']) ? null : Carbon::now(),
        ]);
        // Attach pivot relations. Important behavior:
        // - If only groups are provided (group-only send), attach groups ONLY
        //   and do NOT attach organizations or admins. This ensures a pure
        //   group-mail send does not involve organization recipients or create
        //   org-related DB notifications.
        // - If organizations are provided (org-only or mixed), attach orgs,
        //   admins and also attach groups when both are explicitly provided.
        $hasGroups = !empty($data['group_ids']);
        $hasOrgs = !empty($data['organization_ids']);

        if ($hasGroups && !$hasOrgs) {
            // group-only: attach groups only
            $announcement->groups()->attach($data['group_ids']);
        } else {
            // org-only or mixed: attach orgs/admins if present
            if ($hasOrgs) {
                $announcement->organizations()->attach($data['organization_ids']);
            }
            if (!empty($data['admin_ids'])) {
                $announcement->admins()->attach($data['admin_ids']);
            }
            // attach groups as well if provided (mixed case)
            if ($hasGroups) {
                $announcement->groups()->attach($data['group_ids']);
            }
        }
        \Log::info('[AnnouncementController@send] scheduled_at saved', ['scheduled_at' => $announcement->scheduled_at]);
        // If scheduled, queue it. Otherwise, send now.
        if (isset($data['scheduled_at'])) {
            // You can dispatch a job to send later
        } else {
            // Dispatch the normal announcement flow (org notifications + admin mails)
            // if organizations were provided.
            if ($hasOrgs) {
                $this->dispatchAnnouncement($announcement);
            }

            // Send mail-only to group members if any remain after deduplication
            if (!empty($groupMemberEmails)) {
                foreach ($groupMemberEmails as $email) {
                    $email = trim((string)$email);
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        LaravelNotification::route('mail', $email)->notify(new GeneralNotification($announcement));
                    } else {
                        \Log::warning('[Send] Skipping invalid group member email post-dispatch', ['announcement_id' => $announcement->id, 'email' => $email]);
                    }
                }
            }
        }
        return response()->json(['success' => true, 'announcement' => $announcement]);
    }

    // Fetch announcements for a user
    public function userAnnouncements(Request $request)
    {
        $user = auth()->user();
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
    public function markAsRead($id)
    {
        $user = auth()->user();
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
        'notifications' => $user->notifications // send back updated list
    ]);
}

    // Dispatch announcement (send email + store)
    protected function dispatchAnnouncement(Announcement $announcement)
    {
        // Read attachments from pivot tables
        $attachedGroups = $announcement->groups()->get();
        $attachedOrgs = $announcement->organizations()->get();
        $attachedAdmins = $announcement->admins()->pluck('users.id')->toArray();

        $groupMemberEmails = [];
        foreach ($attachedGroups as $group) {
            if (method_exists($group, 'members')) {
                try {
                    $groupMemberEmails = array_merge($groupMemberEmails, $group->members()->pluck('email')->toArray());
                } catch (\Exception $e) {
                    \Log::warning('[Dispatch] Failed to pluck group members', ['group_id' => $group->id, 'error' => $e->getMessage()]);
                }
            }
        }
        $groupMemberEmails = array_values(array_unique(array_filter($groupMemberEmails)));

        // Collect org-related recipients (DB notifications + admin emails)
        $orgUserIds = [];
        $orgAdminEmails = [];
        foreach ($attachedOrgs as $org) {
            if (method_exists($org, 'users')) {
                try {
                    $orgUserIds = array_merge($orgUserIds, $org->users()->pluck('users.id')->toArray());
                } catch (\Exception $e) {
                    \Log::warning('[Dispatch] Failed to pluck org users', ['org_id' => $org->id, 'error' => $e->getMessage()]);
                }
            }
            $orgAdminEmail = $org->admin_email ?? ($org->user->email ?? null);
            if (!empty($orgAdminEmail) && filter_var($orgAdminEmail, FILTER_VALIDATE_EMAIL)) {
                $orgAdminEmails[] = $orgAdminEmail;
            }
        }

        // Merge admin ids provided explicitly
        $orgUserIds = array_values(array_unique(array_filter(array_merge($orgUserIds, $attachedAdmins))));
        $orgAdminEmails = array_values(array_unique(array_filter($orgAdminEmails)));

        // Send DB notifications to Org users (if any)
        $users = $orgUserIds ? User::whereIn('id', $orgUserIds)->get() : collect();
        if ($users->isNotEmpty()) {
            LaravelNotification::send($users, new GeneralNotification($announcement));
        }

        // Mail addresses originating from org admin fields
        $userEmails = $users->pluck('email')->filter()->unique()->values()->toArray();
        $emailsFromOrgs = array_values(array_unique(array_filter(array_merge($orgAdminEmails, $userEmails))));

        // Now decide sending strategy:
        // - If only groups attached: send only to groupMemberEmails (mail-only)
        // - If only orgs attached: send to org recipients (users + admin emails)
        // - If both attached: send org recipients via DB+mail, and also mail remaining groupMemberEmails (deduped)

        if (!empty($groupMemberEmails) && empty($emailsFromOrgs)) {
            // group-only
            foreach ($groupMemberEmails as $email) {
                $email = trim((string)$email);
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    LaravelNotification::route('mail', $email)->notify(new GeneralNotification($announcement));
                }
            }
            return;
        }

        if (empty($groupMemberEmails) && !empty($emailsFromOrgs)) {
            // org-only
            foreach ($emailsFromOrgs as $email) {
                $email = trim((string)$email);
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    LaravelNotification::route('mail', $email)->notify(new GeneralNotification($announcement));
                }
            }
            return;
        }

        if (!empty($groupMemberEmails) && !empty($emailsFromOrgs)) {
            // mixed: send org recipients (DB already done), then mail remaining group members
            foreach ($emailsFromOrgs as $email) {
                $email = trim((string)$email);
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    LaravelNotification::route('mail', $email)->notify(new GeneralNotification($announcement));
                }
            }
            // subtract org emails from groupMemberEmails
            $remaining = array_values(array_diff($groupMemberEmails, $emailsFromOrgs));
            foreach ($remaining as $email) {
                $email = trim((string)$email);
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    LaravelNotification::route('mail', $email)->notify(new GeneralNotification($announcement));
                }
            }
            return;
        }
    }
}