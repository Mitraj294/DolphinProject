<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\NotificationResource;
use App\Jobs\SendScheduledAnnouncementJob;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Fetch all notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $notifications = $user->notifications()->orderByDesc('created_at')->get();

        return NotificationResource::collection($notifications)->response();
    }

    /**
     * Fetch only the unread notifications for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unread(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $unreadNotifications = $user->unreadNotifications()->get();

        return NotificationResource::collection($unreadNotifications)->response();
    }

    /**
     * Mark a specific notification as read.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    /**
     * Mark all unread notifications as read for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /**
     * [Admin] Get a list of all announcements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAnnouncements(): JsonResponse
    {
        $announcements = Announcement::orderByDesc('created_at')->get();
        return response()->json(['data' => $announcements]);
    }

    /**
     * [Admin] Get a single announcement and its recipient details.
     *
     * @param \App\Models\Announcement $announcement
     * @return \App\Http\Resources\AnnouncementResource
     */
    public function showAnnouncement(Announcement $announcement): AnnouncementResource
    {
        // The complex data transformation is now handled by the AnnouncementResource class.
        return new AnnouncementResource($announcement);
    }

    /**
     * [Admin] Create and dispatch a new announcement.
     *
     * @param \App\Http\Requests\StoreAnnouncementRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAnnouncement(StoreAnnouncementRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            /** @var Announcement $announcement */
            $announcement = Announcement::create([
                'body' => $validated['body'],
                'sender_id' => Auth::id(),
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);

            // Attach recipients to the announcement
            if (!empty($validated['organization_ids'])) {
                $announcement->organizations()->attach($validated['organization_ids']);
            }
            if (!empty($validated['group_ids'])) {
                $announcement->groups()->attach($validated['group_ids']);
            }
            if (!empty($validated['user_ids'])) {
                $announcement->users()->attach($validated['user_ids']);
            }

            // If the announcement is not scheduled for the future, dispatch it immediately.
            // Otherwise, the scheduled command will pick it up.
            if (!$announcement->scheduled_at) {
                SendScheduledAnnouncementJob::dispatch($announcement);
            }

            return response()->json([
                'message' => 'Announcement created successfully.',
                'data' => new AnnouncementResource($announcement),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create announcement.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
