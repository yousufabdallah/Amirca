<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\FileStore;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public const TEXT_KEYS = [
        'office_name', 'office_tagline', 'phone', 'whatsapp', 'email', 'address', 'working_hours', 'map_url',
        'facebook', 'instagram', 'x', 'tiktok', 'snapchat', 'youtube',
        'currency', 'date_format', 'receipt_prefix', 'receipt_start', 'receipt_footer', 'receipt_terms', 'documents_default_visible',
        'hero_title', 'hero_subtitle', 'hero_badge', 'about_title', 'about_text', 'home_services', 'home_steps', 'home_why',
        'contact_text', 'footer_text', 'stat_1_value', 'stat_1_label', 'stat_2_value', 'stat_2_label', 'stat_3_value', 'stat_3_label',
    ];

    public function edit()
    {
        return view('admin.settings.edit', ['s' => Setting::allCached()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'office_name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'currency' => 'required|string|size:3',
            'date_format' => 'required|in:d/m/Y,Y-m-d,d-m-Y',
            'receipt_start' => 'nullable|integer|min:1',
            'receipt_prefix' => 'nullable|string|max:10|regex:/^[A-Za-z0-9\-]*$/',
            'map_url' => 'nullable|url|max:1000',
            'facebook' => 'nullable|url|max:300', 'instagram' => 'nullable|url|max:300', 'x' => 'nullable|url|max:300',
            'tiktok' => 'nullable|url|max:300', 'snapchat' => 'nullable|url|max:300', 'youtube' => 'nullable|url|max:300',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
            'hero' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            'about' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:8192',
            '*' => 'nullable',
        ], [], ['office_name' => 'اسم المكتب', 'currency' => 'العملة']);

        $values = [];
        foreach (self::TEXT_KEYS as $k) {
            if ($request->has($k)) {
                $values[$k] = mb_substr((string) $request->input($k), 0, 10000);
            }
        }
        $values['currency'] = strtoupper($values['currency'] ?? 'OMR');
        $values['documents_default_visible'] = $request->boolean('documents_default_visible') ? '1' : '0';
        foreach (['logo', 'hero', 'about'] as $img) {
            if ($file = $request->file($img)) {
                FileStore::delete(Setting::get('media_'.$img));
                $values['media_'.$img] = FileStore::storeImage($file, 'site');
            } elseif ($request->boolean('remove_'.$img)) {
                FileStore::delete(Setting::get('media_'.$img));
                $values['media_'.$img] = null;
            }
        }
        Setting::put($values);
        AuditLog::record('settings.update', 'تحديث إعدادات النظام والموقع');

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
