<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function authCheck()
    {
        if (!session('admin_logged_in')) return redirect()->route('admin.login');
        return null;
    }

    public function index()
    {
        if ($r = $this->authCheck()) return $r;
        $notifications = AdminNotification::orderBy('created_at', 'desc')->paginate(30);
        $unreadCount   = AdminNotification::unreadCount();
        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * SSE feed — streams new notifications since last_id.
     */
    public function feed(Request $request)
    {
        if (!session('admin_logged_in')) abort(403);
        $lastId = (int) $request->get('last_id', 0);

        return response()->stream(function () use ($lastId) {
            $notifications = AdminNotification::where('id', '>', $lastId)
                ->where('read', false)
                ->orderBy('id')
                ->get();

            $unread = AdminNotification::where('read', false)->count();

            echo "event: update\n";
            echo 'data: ' . json_encode([
                'notifications' => $notifications->map(fn($n) => [
                    'id'        => $n->id,
                    'title'     => $n->title,
                    'message'   => $n->message,
                    'icon'      => $n->icon,
                    'color'     => $n->color,
                    'link'      => $n->link,
                    'read'      => $n->read,
                    'time'      => $n->created_at->diffForHumans(),
                ]),
                'unread_count'  => $unread,
                'last_id'       => AdminNotification::max('id') ?? $lastId, // Get absolute max ID
            ]) . "\n\n";
            ob_flush(); flush();
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function markRead($id)
    {
        if ($r = $this->authCheck()) return $r;
        AdminNotification::findOrFail($id)->update(['read' => true]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        if ($r = $this->authCheck()) return $r;
        AdminNotification::where('read', false)->update(['read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy($id)
    {
        if ($r = $this->authCheck()) return $r;
        AdminNotification::findOrFail($id)->delete();
        return back()->with('success', 'Notification deleted.');
    }
}