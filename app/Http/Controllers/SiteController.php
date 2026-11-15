<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Services\FileStore;
use App\Services\Notifier;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        return view('site.home', [
            'services' => self::lines(setting('home_services')),
            'steps' => self::lines(setting('home_steps')),
            'why' => self::lines(setting('home_why')),
            'slides' => self::slides(),
        ]);
    }

    /** تحويل نص متعدد الأسطر بصيغة "العنوان | الوصف | الأيقونة" إلى مصفوفة */
    public static function lines(?string $text): array
    {
        $out = [];
        foreach (preg_split('/\r?\n/', (string) $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            $out[] = ['title' => $parts[0] ?? '', 'text' => $parts[1] ?? '', 'icon' => $parts[2] ?? 'bi-check2-circle'];
        }

        return $out;
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'message' => 'required|string|max:2000',
            'website' => 'nullable|size:0', // honeypot ضد البريد المزعج
        ], [], ['name' => 'الاسم', 'phone' => 'رقم الهاتف', 'message' => 'الرسالة']);
        unset($data['website']);
        $inq = Inquiry::create($data + ['ip' => $request->ip()]);
        Notifier::staff('inquiry', 'استفسار جديد من الموقع', $inq->name.' — '.$inq->phone, route('admin.inquiries.index'), 'bi-envelope', 'inquiries.view');

        return redirect(route('home').'#contact')->with('contact_ok', 'تم استلام رسالتك بنجاح، وسنتواصل معك قريبًا.');
    }

    /** صور العرض المتغير في الصفحة الرئيسية (مسارات محفوظة في الإعدادات) */
    public static function slides(): array
    {
        $list = json_decode((string) setting('hero_slides', '[]'), true);

        return is_array($list) ? array_values(array_filter($list, 'is_string')) : [];
    }

    public function slide(int $index)
    {
        $path = self::slides()[$index] ?? null;
        abort_unless($path, 404);

        return FileStore::response($path);
    }

    public function media(string $key)
    {
        $path = setting('media_'.$key);
        abort_unless($path, 404);

        return FileStore::response($path);
    }
}
