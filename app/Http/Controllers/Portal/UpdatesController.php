<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\VehicleStatusHistory;
use Illuminate\Http\Request;

class UpdatesController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');

        return view('portal.updates', [
            'notifications' => AppNotification::where('user_id', $request->user()->id)->latest('id')->paginate(20),
            'updates' => VehicleStatusHistory::with(['vehicle:id,make,model,year,file_no', 'toStatus'])
                ->whereIn('vehicle_id', $client->vehicles()->pluck('id'))->where('visible_to_client', true)->latest('created_at')->limit(30)->get(),
        ]);
    }

    public function open(Request $request, int $notification)
    {
        $n = AppNotification::where('user_id', $request->user()->id)->findOrFail($notification);
        $n->update(['read_at' => $n->read_at ?? now()]);

        return redirect($n->url && str_starts_with($n->url, url('/portal')) ? $n->url : route('portal.updates'));
    }

    public function readAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }
}
