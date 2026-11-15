<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->only(['user_id', 'action', 'from', 'to', 'q']);
        $logs = AuditLog::with('user:id,name,role')
            ->when(! empty($f['user_id']), fn ($q) => $q->where('user_id', $f['user_id']))
            ->when(! empty($f['action']), fn ($q) => $q->where('action', 'like', $f['action'].'%'))
            ->when(! empty($f['from']), fn ($q) => $q->whereDate('created_at', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->whereDate('created_at', '<=', $f['to']))
            ->when(! empty($f['q']), fn ($q) => $q->where('description', 'like', '%'.addcslashes($f['q'], '%_\\').'%'))
            ->latest('id')->paginate(40)->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs, 'f' => $f,
            'users' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get(['id', 'name']),
            'actions' => ['client' => 'العملاء', 'vehicle' => 'السيارات', 'payment' => 'الدفعات', 'expense' => 'المصروفات', 'document' => 'المستندات', 'user' => 'الموظفون', 'auth' => 'الدخول', 'settings' => 'الإعدادات', 'status' => 'المراحل'],
        ]);
    }
}
