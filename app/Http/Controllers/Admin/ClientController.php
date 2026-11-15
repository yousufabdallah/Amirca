<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\Services\Notifier;
use App\Support\ClientAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $like = '%'.addcslashes($q, '%_\\').'%';
        $state = $request->query('state', 'active');

        $clients = Client::query()->select('clients.*')->withFinancials()->with('user:id,is_active,username')
            ->when($state === 'archived', fn ($w) => $w->whereNotNull('archived_at'), fn ($w) => $w->whereNull('archived_at'))
            ->when($state === 'inactive', fn ($w) => $w->where('is_active', false))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('full_name', 'like', $like)->orWhere('phone', 'like', $like)
                ->orWhere('phone_alt', 'like', $like)->orWhere('client_no', 'like', $like)->orWhere('civil_id_hash', Client::civilHash($q))))
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.clients.index', compact('clients', 'q', 'state'));
    }

    public function create()
    {
        return view('admin.clients.form', ['client' => new Client(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $account = null;
        $client = DB::transaction(function () use ($data, $request, &$account) {
            $client = new Client($data);
            $client->setCivilId($data['civil_id'] ?? null);
            $client->created_by = $request->user()->id;
            $client->save();
            if ($request->boolean('create_account')) {
                $account = ClientAccounts::create($client, $request->input('username'), $request->input('password'));
            }

            return $client;
        });

        AuditLog::record('client.create', 'إضافة عميل جديد: '.$client->full_name.' ('.$client->client_no.')', $client);
        Notifier::staff('client.new', 'تم إضافة عميل جديد', $client->full_name.' — '.$client->client_no, route('admin.clients.show', $client), 'bi-person-plus', 'clients.view');

        $redirect = redirect()->route('admin.clients.show', $client)->with('success', 'تم حفظ العميل بنجاح برقم '.$client->client_no);
        if ($account) {
            $redirect->with('credentials', $account);
        }

        return $redirect;
    }

    public function show(Client $client)
    {
        $client->load(['user', 'creator:id,name', 'internalNotes.user:id,name', 'documents' => fn ($q) => $q->latest(), 'documents.vehicle:id,file_no,make,model']);
        $vehicles = $client->vehicles()->with(['status', 'primaryImage'])->withPaid()->latest('id')->get();
        $payments = $client->payments()->with(['vehicle:id,file_no,make,model,year', 'creator:id,name'])->latest('paid_at')->latest('id')->get();
        $logs = AuditLog::with('user:id,name')->where(function ($q) use ($client, $vehicles) {
            $q->where(fn ($x) => $x->where('subject_type', 'Client')->where('subject_id', $client->id))
                ->orWhere(fn ($x) => $x->where('subject_type', 'Vehicle')->whereIn('subject_id', $vehicles->pluck('id')))
                ->orWhere(fn ($x) => $x->where('subject_type', 'Payment')->whereIn('subject_id', $client->payments()->pluck('id')));
        })->latest('id')->limit(40)->get();

        return view('admin.clients.show', [
            'client' => $client,
            'vehicles' => $vehicles,
            'payments' => $payments,
            'fin' => $client->financials(),
            'logs' => $logs,
        ]);
    }

    public function edit(Client $client)
    {
        return view('admin.clients.form', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $this->validated($request, $client);
        $before = $client->only(['full_name', 'phone', 'phone_alt', 'email', 'governorate', 'wilayat', 'address', 'is_active']);
        $client->fill($data);
        if ($request->filled('civil_id')) {
            $client->setCivilId($data['civil_id']);
        } elseif ($request->boolean('clear_civil_id')) {
            $client->setCivilId(null);
        }
        $civilChanged = $client->isDirty('civil_id_hash');
        $client->save();

        if ($client->user) {
            $client->user->update(['name' => $client->full_name, 'email' => $client->email ?: null]);
        }
        $after = $client->only(array_keys($before));
        $changes = [];
        foreach ($before as $k => $v) {
            if ($v != $after[$k]) {
                $changes[$k] = ['from' => $v, 'to' => $after[$k]];
            }
        }
        AuditLog::record('client.update', 'تعديل بيانات العميل '.$client->full_name.' ('.$client->client_no.')'.($civilChanged ? ' - تم تعديل الرقم المدني' : ''), $client, $changes ?: null);

        return redirect()->route('admin.clients.show', $client)->with('success', 'تم تحديث بيانات العميل.');
    }

    public function archive(Client $client)
    {
        $client->update(['is_active' => false]);
        $client->forceFill(['archived_at' => now()])->save();
        $client->user?->update(['is_active' => false]);
        AuditLog::record('client.archive', 'أرشفة العميل '.$client->full_name.' ('.$client->client_no.')', $client);

        return redirect()->route('admin.clients.index')->with('success', 'تمت أرشفة العميل وتعطيل حسابه. يمكن استعادته من الأرشيف.');
    }

    public function restore(Client $client)
    {
        $client->forceFill(['archived_at' => null, 'is_active' => true])->save();
        AuditLog::record('client.restore', 'استعادة العميل '.$client->full_name.' من الأرشيف', $client);

        return redirect()->route('admin.clients.show', $client)->with('success', 'تمت استعادة العميل. فعّل حساب الدخول إذا لزم.');
    }

    protected function validated(Request $request, ?Client $client = null): array
    {
        $civilRule = function ($attr, $value, $fail) use ($client) {
            $exists = Client::where('civil_id_hash', Client::civilHash($value))->when($client, fn ($q) => $q->where('id', '<>', $client->id))->exists();
            if ($exists) {
                $fail('الرقم المدني مسجل لعميل آخر.');
            }
        };

        return $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:30|regex:/^[0-9+\s\-]{6,30}$/',
            'phone_alt' => 'nullable|string|max:30|regex:/^[0-9+\s\-]{6,30}$/',
            'civil_id' => ['nullable', 'string', 'max:20', 'regex:/^[0-9A-Za-z]{4,20}$/', $civilRule],
            'email' => 'nullable|email|max:150',
            'governorate' => 'nullable|string|max:100',
            'wilayat' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'internal_notes' => 'nullable|string|max:5000',
            'is_active' => 'sometimes|boolean',
            'username' => ['nullable', 'string', 'min:3', 'max:60', 'regex:/^[A-Za-z0-9_.\-]+$/', Rule::unique('users', 'username')],
            'password' => 'nullable|string|min:8|max:100',
        ], [], [
            'full_name' => 'الاسم الكامل', 'phone' => 'رقم الهاتف', 'phone_alt' => 'الهاتف البديل', 'civil_id' => 'الرقم المدني',
            'email' => 'البريد الإلكتروني', 'username' => 'اسم المستخدم', 'password' => 'كلمة المرور',
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
