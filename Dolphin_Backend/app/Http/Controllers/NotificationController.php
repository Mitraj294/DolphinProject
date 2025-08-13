<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use App\Notifications\SendNotification;
use Carbon\Carbon;

class NotificationController extends Controller {
    // Return all notifications (for superadmin or testing)
    // Public: Return all notifications (no auth required)
    public function allNotifications()
    {
        $notifications = Notification::orderByDesc('created_at')->get();
        return response()->json($notifications);
    }

    // Send notification to orgs, admins, groups
    public function send(Request $request)
    {
        $data = $request->validate([
            'body' => 'required|string',
            'organization_ids' => 'nullable|array',
            'admin_ids' => 'nullable|array',
            'group_ids' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ]);
        $notification = Notification::create([
            'body' => $data['body'],
            'sender_id' => auth()->id(),
            'organization_ids' => json_encode($data['organization_ids'] ?? []),
            'admin_ids' => json_encode($data['admin_ids'] ?? []),
            'group_ids' => json_encode($data['group_ids'] ?? []),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'sent_at' => isset($data['scheduled_at']) ? null : Carbon::now(),
        ]);
        // If scheduled, queue it. Otherwise, send now.
        if (isset($data['scheduled_at'])) {
            // You can dispatch a job to send later
        } else {
            // Send notification now
            $this->dispatchNotification($notification);
        }
        return response()->json(['success' => true, 'notification' => $notification]);
    }

    // Fetch notifications for a user
    public function userNotifications(Request $request)
    {
        $user = auth()->user();
        $notifications = Notification::whereJsonContains('admin_ids', $user->id)
            ->orWhereJsonContains('organization_ids', $user->organization_id)
            ->orWhereJsonContains('group_ids', $user->group_id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['notifications' => $notifications]);
    }

    // Dispatch notification (send email + store)
    protected function dispatchNotification(Notification $notification)
    {
        // Find users by orgs, admins, groups
        $userIds = array_merge(
            json_decode($notification->admin_ids, true) ?? [],
            // You can add logic to get users from orgs/groups
        );
        $users = User::whereIn('id', $userIds)->get();
        LaravelNotification::send($users, new SendNotification($notification));
    }
}
