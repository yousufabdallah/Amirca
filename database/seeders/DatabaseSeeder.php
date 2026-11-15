<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\VehicleStatus;
use Illuminate\Database\Seeder;

/** البيانات الأساسية: حساب المدير، مراحل الاستيراد الافتراضية، إعدادات الموقع */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::where('role', 'admin')->exists()) {
            User::create([
                'name' => env('ADMIN_NAME', 'مدير النظام'),
                'username' => env('ADMIN_USERNAME', 'admin'),
                'email' => env('ADMIN_EMAIL') ?: null,
                'password' => env('ADMIN_PASSWORD', 'ChangeMe@2026'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        if (! VehicleStatus::exists()) {
            $statuses = [
                ['تم استلام الطلب', '#64748b', 'bi-inbox', 'pre_purchase'],
                ['البحث عن السيارة', '#6366f1', 'bi-search', 'pre_purchase'],
                ['تم شراء السيارة', '#0ea5e9', 'bi-bag-check', 'purchased'],
                ['في ساحة المزاد', '#0891b2', 'bi-building', 'purchased'],
                ['في النقل الداخلي بأمريكا', '#0d9488', 'bi-truck', 'purchased'],
                ['تم الشحن من أمريكا', '#2563eb', 'bi-box-seam', 'shipping'],
                ['في الطريق إلى سلطنة عُمان', '#1d4ed8', 'bi-water', 'shipping'],
                ['وصلت إلى ميناء عُمان', '#c8a24a', 'bi-geo-alt-fill', 'arrived'],
                ['إجراءات التخليص الجمركي', '#d97706', 'bi-file-earmark-check', 'arrived'],
                ['جاهزة للتسليم', '#16a34a', 'bi-key', 'arrived'],
                ['تم التسليم', '#15803d', 'bi-patch-check-fill', 'delivered'],
            ];
            foreach ($statuses as $i => [$name, $color, $icon, $group]) {
                VehicleStatus::create(['name' => $name, 'color' => $color, 'icon' => $icon, 'stage_group' => $group, 'sort_order' => $i + 1, 'is_active' => true]);
            }
        }

        $defaults = [
            'office_name' => 'العميد للاستيراد والتصدير',
            'office_tagline' => 'استيراد السيارات من الولايات المتحدة إلى سلطنة عُمان',
            'phone' => '+968 9000 0000',
            'whatsapp' => '96890000000',
            'email' => 'info@alameed.om',
            'address' => 'سلطنة عُمان - مسقط',
            'working_hours' => 'السبت - الخميس: 9 صباحًا - 9 مساءً',
            'currency' => 'OMR',
            'date_format' => 'd/m/Y',
            'receipt_start' => '1001',
            'receipt_prefix' => '',
            'receipt_footer' => 'شكرًا لتعاملكم مع العميد للاستيراد والتصدير',
            'receipt_terms' => 'هذا الإيصال صادر إلكترونيًا ولا يحتاج إلى توقيع.',
            'documents_default_visible' => '1',
            'hero_badge' => 'خبرة موثوقة في استيراد السيارات',
            'hero_title' => 'سيارتك من أمريكا إلى باب بيتك في عُمان',
            'hero_subtitle' => 'نشتري لك من أكبر المزادات الأمريكية، ونتولى الشحن والتخليص الجمركي، وتتابع كل مرحلة لحظة بلحظة من حسابك الخاص.',
            'about_title' => 'من نحن',
            'about_text' => "العميد للاستيراد والتصدير مكتب عُماني متخصص في استيراد السيارات من الولايات المتحدة الأمريكية.\nنرافقك في كل خطوة: من اختيار السيارة المناسبة وشرائها من المزادات الموثوقة، مرورًا بالنقل والشحن البحري، وصولًا إلى التخليص الجمركي وتسليمها لك جاهزة.",
            'home_services' => "شراء السيارات من المزادات | نشتري لك من Copart وIAAI وManheim وغيرها بعد فحص التقارير والصور | bi-hammer\nالنقل الداخلي في أمريكا | ننقل السيارة من ساحة المزاد إلى ميناء الشحن بأمان | bi-truck\nالشحن البحري إلى عُمان | شحن بالحاويات أو RoRo مع شركات شحن معتمدة | bi-water\nالتخليص الجمركي | ننهي جميع الإجراءات الجمركية والأوراق الرسمية نيابة عنك | bi-file-earmark-check\nمتابعة إلكترونية | حساب خاص تتابع فيه سيارتك ودفعاتك وصورها في أي وقت | bi-phone\nاستشارة قبل الشراء | نساعدك في اختيار السيارة المناسبة لميزانيتك | bi-chat-dots",
            'home_steps' => "اطلب سيارتك | أخبرنا بالسيارة التي تريدها وميزانيتك\nالبحث والشراء | نبحث في المزادات ونشتري السيارة بعد موافقتك\nالنقل والشحن | ننقل السيارة إلى الميناء ونشحنها إلى عُمان\nالتخليص والتسليم | نخلّص الإجراءات الجمركية ونسلمك السيارة",
            'home_why' => "شفافية كاملة | تشاهد كل التكاليف والدفعات والمتبقي في حسابك | bi-eye\nمتابعة لحظية | تعرف أين وصلت سيارتك وموعد وصولها المتوقع | bi-broadcast\nخبرة وثقة | تعامل مع مزادات وشركات شحن موثوقة | bi-shield-check\nأسعار منافسة | تكاليف واضحة بدون رسوم مخفية | bi-tags",
            'contact_text' => 'لديك استفسار أو ترغب في استيراد سيارة؟ راسلنا وسنتواصل معك في أقرب وقت.',
            'footer_text' => 'شريكك الموثوق في استيراد السيارات من الولايات المتحدة الأمريكية إلى سلطنة عُمان.',
            'stat_1_value' => '+500', 'stat_1_label' => 'سيارة تم استيرادها',
            'stat_2_value' => '+10', 'stat_2_label' => 'سنوات خبرة',
            'stat_3_value' => '100%', 'stat_3_label' => 'متابعة إلكترونية',
        ];
        foreach ($defaults as $k => $v) {
            Setting::firstOrCreate(['key' => $k], ['value' => $v]);
        }
        \Illuminate\Support\Facades\Cache::forget('settings.all');
    }
}
