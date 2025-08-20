<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
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
            $notifications = \DB::table('notifications')->orderByDesc('created_at')->get();
            // Decode payloads
            $notifications->transform(function ($n) {
                if (isset($n->data) && is_string($n->data)) {
                    $n->data = json_decode($n->data, true);
                }
                return $n;
            });
            return response()->json(['notifications' => $notifications]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch all notifications', ['error' => $e->getMessage()]);
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
        // Attach relationships
        if (!empty($data['organization_ids'])) {
            $announcement->organizations()->attach($data['organization_ids']);
        }
        if (!empty($data['admin_ids'])) {
            $announcement->admins()->attach($data['admin_ids']);
        }
        // Only attach groups if the request explicitly provided group_ids.
        // If this is an organization-only send (no group_ids present), ensure
        // any pre-existing group links are not used so member emails are not
        // included accidentally.
        if (!empty($data['group_ids'])) {
            $announcement->groups()->attach($data['group_ids']);
        } else {
            // defensive: detach any groups to guarantee organization-only sends
            // do not include group member emails.
            $announcement->groups()->detach();
        }
        \Log::info('[AnnouncementController@send] scheduled_at saved', ['scheduled_at' => $announcement->scheduled_at]);
        // If scheduled, queue it. Otherwise, send now.
        if (isset($data['scheduled_at'])) {
            // You can dispatch a job to send later
        } else {
            // Send announcement now
            $this->dispatchAnnouncement($announcement);
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
    // Find users by orgs, admins, groups — dedupe IDs to avoid duplicate notifications/emails
    $userIds = [];
    // Collect member emails (members are separate from users in this app)
    $memberEmails = [];
    // Get admin IDs
    $userIds = array_merge($userIds, $announcement->admins()->pluck('users.id')->toArray());
        // Organization handling:
        // - DB notifications: include any User models associated with the org (via org->users())
        // - Mail: only send to the organization's admin_email (if present) to avoid mailing every member
    foreach ($announcement->organizations as $org) {
            if (method_exists($org, 'users')) {
                // safe attempt to collect user ids for DB notifications
                try {
                    $userIds = array_merge($userIds, $org->users()->pluck('users.id')->toArray());
                } catch (\Exception $e) {
                    // if the users relation or schema is not available, skip DB collection for safety
                    \Log::warning('[Dispatch] Failed to pluck org users', ['org_id' => $org->id, 'error' => $e->getMessage()]);
                }
            }
            // For mail, include only the organization's admin contact email (if available)
            if (!empty($org->admin_email) && filter_var($org->admin_email, FILTER_VALIDATE_EMAIL)) {
                $memberEmails[] = $org->admin_email;
            }
        }

        // For groups: collect member emails only when groups are actually attached
        // to this announcement. If this is an organization-only announcement
        // announcement->groups will be empty and we must not collect group members.
        if ($announcement->groups && $announcement->groups->isNotEmpty()) {
            foreach ($announcement->groups as $group) {
                if (method_exists($group, 'members')) {
                    $emails = $group->members()->pluck('email')->toArray();
                    $memberEmails = array_merge($memberEmails, $emails);
                }
                // intentionally skip $group->users() and member->user_id handling
            }
        }

        // Clean up lists
        $userIds = array_values(array_unique(array_filter($userIds)));
        $memberEmails = array_values(array_unique(array_filter($memberEmails)));

        // Fetch unique users for DB + mail (via Notification)
        $users = $userIds ? User::whereIn('id', $userIds)->get() : collect();

        // Send notifications to User models (this will create database entries + mail)
        if ($users->isNotEmpty()) {
            LaravelNotification::send($users, new GeneralNotification($announcement));
        }

        // Avoid sending duplicate emails to addresses that belong to User models
        $userEmails = $users->pluck('email')->filter()->unique()->values()->toArray();
        $emailsToSend = array_values(array_diff($memberEmails, $userEmails));

        // Send mail-only notifications to remaining member emails
        foreach ($emailsToSend as $email) {
            $email = trim((string)$email);
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Use Notification routing to send mail to raw email addresses
                LaravelNotification::route('mail', $email)->notify(new GeneralNotification($announcement));
            } else {
                \Log::warning('[Dispatch] Skipping invalid member email', ['announcement_id' => $announcement->id, 'email' => $email]);
            }
        }
    }
}