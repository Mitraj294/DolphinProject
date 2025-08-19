<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use App\Notifications\SendNotification;
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
        if (!empty($data['group_ids'])) {
            $announcement->groups()->attach($data['group_ids']);
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
    public function markAllRead()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        try {
            $now = Carbon::now();
            // Update notifications table directly to ensure read_at is persisted
            \DB::table('notifications')
                ->whereNull('read_at')
                ->where(function($q) use ($user) {
                    // notifications directly for this user
                    $q->where(function($qq) use ($user) {
                        $qq->where('notifiable_type', User::class)
                           ->where('notifiable_id', $user->id);
                    });

                    // notifications for the user's organization (if any)
                    if (!empty($user->organization_id)) {
                        $q->orWhere(function($qq) use ($user) {
                            $qq->where('notifiable_type', '\\App\\Models\\Organization')
                               ->where('notifiable_id', $user->organization_id);
                        });
                    }

                    // notifications for the user's group (if any)
                    if (!empty($user->group_id)) {
                        $q->orWhere(function($qq) use ($user) {
                            $qq->where('notifiable_type', '\\App\\Models\\Group')
                               ->where('notifiable_id', $user->group_id);
                        });
                    }
                })
                ->update(['read_at' => $now]);

            // Also attempt to mark collection items (if loaded) for consistency
            if ($user->relationLoaded('unreadNotifications')) {
                foreach ($user->unreadNotifications as $n) {
                    $n->read_at = $now;
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to mark all notifications as read', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to mark all as read'], 500);
        }
    }
    // Dispatch announcement (send email + store)
    protected function dispatchAnnouncement(Announcement $announcement)
    {
        // Find users by orgs, admins, groups
        $userIds = [];
        // Get admin IDs
        $userIds = array_merge($userIds, $announcement->admins()->pluck('users.id')->toArray());
        // Get organization user IDs
        foreach ($announcement->organizations as $org) {
            if (method_exists($org, 'users')) {
                $userIds = array_merge($userIds, $org->users()->pluck('users.id')->toArray());
            }
        }
        // Get group user IDs
        foreach ($announcement->groups as $group) {
            if (method_exists($group, 'users')) {
                $userIds = array_merge($userIds, $group->users()->pluck('users.id')->toArray());
            }
        }
        $users = User::whereIn('id', $userIds)->get();
        LaravelNotification::send($users, new SendNotification($announcement));
    }
}