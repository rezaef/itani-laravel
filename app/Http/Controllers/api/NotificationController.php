<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $limit = (int) $request->query('limit', 8);
        $limit = max(1, min($limit, 30));

        $onlyUnread = $request->query('unread') == '1';

        $q = SensorNotification::query()
            // broadcast + per-user
            ->where(function ($qq) {
                $qq->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->orderByDesc('created_at');

        if ($onlyUnread) $q->where('is_read', false);

        $items = $q->limit($limit)->get([
            'id','level','title','message','is_read','created_at'
        ]);

        $unreadCount = SensorNotification::query()
            ->where(function ($qq) {
                $qq->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'items' => $items,
        ]);
    }

    public function markRead(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $payload = $request->json()->all();
        $ids = $payload['ids'] ?? [];
        if (!is_array($ids)) $ids = [];

        $ids = array_values(array_unique(array_filter($ids, fn($v) => is_numeric($v))));
        if (!$ids) return response()->json(['success' => true, 'updated' => 0]);

        $updated = SensorNotification::query()
            ->whereIn('id', $ids)
            ->where(function ($qq) {
                $qq->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->update(['is_read' => true]);

        return response()->json(['success' => true, 'updated' => $updated]);
    }
}
