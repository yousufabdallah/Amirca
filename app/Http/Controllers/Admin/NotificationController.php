<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $items = AppNotification::where('user_id', $request->user()->id)
            ->when($request->query('filter') === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.notifications.index', ['items' => $items, 'filter' => $request->query('filter')]);
    }

    public function count(Request $request)
    {
        $base = AppNotification::where('user_id', $request->user()->id);

        return response()->json([
            'unread' => (clone $base)->whereNull('read_at')->count(),
            'latest' => (clone $base)->latest('id')->limit(8)->get()->map(fn ($n) => [
                'title' => $n->title, 'body' => $n->body, 'icon' => $n->icon, 'unread' => ! $n->read_at,
                'time' => $n->created_at->diffForHumans(), 'url' => route('admin.notifications.open', $n->id),
            ]),
        ]);
    }

    public function open(Request $request, int $notification)
    {
        $n = AppNotification::where('user_id', $request->user()->id)->findOrFail($notification);
        $n->update(['read_at' => $n->read_at ?? now()]);

        return redirect($n->url && str_starts_with($n->url, url('/')) ? $n->url : route('admin.notifications.index'));
    }

    public function readAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }
}
