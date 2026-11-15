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
            'slides' => 'nullable|array|max:20',
            'slides.*' => 'file|mimes:jpg,jpeg,png,webp|max:8192',
            'remove_slides' => 'nullable|array',
            'slide_order' => 'nullable|string|max:500',
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
        // صور العرض المتغير: حذف، ترتيب، ثم إضافة الجديد
        $slides = \App\Http\Controllers\SiteController::slides();
        $remove = array_map('intval', (array) $request->input('remove_slides', []));
        $kept = [];
        foreach ($slides as $i => $p) {
            if (in_array($i, $remove, true)) {
                FileStore::delete($p);
            } else {
                $kept[$i] = $p;
            }
        }
        if ($order = $request->input('slide_order')) {
            $sorted = [];
            foreach (explode(',', $order) as $i) {
                if (isset($kept[(int) $i])) {
                    $sorted[] = $kept[(int) $i];
                    unset($kept[(int) $i]);
                }
            }
            $kept = array_merge($sorted, array_values($kept));
        }
        $kept = array_values($kept);
        foreach ((array) $request->file('slides', []) as $file) {
            $kept[] = FileStore::storeImage($file, 'site/slides');
        }
        $values['hero_slides'] = json_encode(array_slice($kept, 0, 20));
        $values['hero_slide_seconds'] = (string) max(3, min(20, (int) $request->input('hero_slide_seconds', 6)));

        Setting::put($values);
        AuditLog::record('settings.update', 'تحديث إعدادات النظام والموقع');

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
