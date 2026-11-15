<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\VehicleStatus;
use App\Models\VehicleStatusHistory;
use App\Support\ClientAccounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * بيانات تجريبية للتطوير والاختبار فقط — لا تُشغّل على الموقع الحقيقي.
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('لا يمكن تشغيل البيانات التجريبية في بيئة الإنتاج.');

            return;
        }
        auth()->login(User::where('role', 'admin')->first());
        $statuses = VehicleStatus::ordered()->get();
        $cars = [['Toyota', 'Camry', 'SE', 2024, 'أبيض', [240, 240, 245]], ['Lexus', 'ES 350', 'F Sport', 2023, 'أسود', [30, 32, 40]],
            ['Nissan', 'Patrol', 'LE', 2022, 'فضي', [180, 185, 195]], ['Ford', 'Mustang', 'GT', 2021, 'أحمر', [190, 30, 40]],
            ['Hyundai', 'Sonata', 'Limited', 2023, 'أزرق', [30, 70, 160]], ['Chevrolet', 'Tahoe', 'Z71', 2022, 'رمادي', [100, 105, 115]]];
        $names = [['سالم بن محمد البلوشي', '91234567'], ['أحمد بن سعيد الحارثي', '92345678'], ['فاطمة بنت علي الرواحي', '93456789']];

        foreach ($names as $ci => [$name, $phone]) {
            $client = new Client(['full_name' => $name, 'phone' => $phone, 'email' => null, 'governorate' => 'مسقط', 'wilayat' => 'السيب', 'address' => 'الموالح الجنوبية', 'is_active' => true]);
            $client->setCivilId((string) (10000000 + $ci * 1111111));
            $client->save();
            ClientAccounts::create($client, null, 'Client@123');

            foreach (array_slice($cars, $ci * 2, 2) as $k => [$make, $model, $trim, $year, $color, $rgb]) {
                $st = $statuses[[6, 10, 7, 3, 5, 1][$ci * 2 + $k]];
                $v = Vehicle::create(['client_id' => $client->id, 'status_id' => $st->id, 'make' => $make, 'model' => $model, 'trim' => $trim, 'year' => $year,
                    'color' => $color, 'vin' => strtoupper(substr(md5($make.$model), 0, 17)), 'fuel_type' => 'بنزين', 'transmission' => 'أوتوماتيك', 'mileage' => 12000 + $k * 8000,
                    'purchase_location' => 'Houston, TX', 'auction_name' => $k ? 'IAAI' : 'Copart', 'purchase_date' => now()->subDays(40 - $k * 10),
                    'purchase_price' => 3200 + $ci * 500, 'auction_fees' => 250, 'inland_transport' => 180, 'departure_port' => 'Houston, TX', 'arrival_port' => 'ميناء صحار',
                    'shipping_company' => 'MSC', 'vessel_name' => 'MSC AURORA', 'container_no' => 'MSCU'.rand(1000000, 9999999), 'shipping_date' => now()->subDays(20),
                    'eta' => now()->addDays([5, -3, 12, 25, -1, 40][$ci * 2 + $k]), 'arrived_at' => $st->stage_group === 'arrived' || $st->stage_group === 'delivered' ? now()->subDays(2) : null,
                    'total_due' => 5000 + $ci * 1500 + $k * 750, 'currency' => 'OMR']);
                foreach (array_slice($statuses->all(), 0, $statuses->search(fn ($s) => $s->id === $st->id) + 1) as $i => $s) {
                    VehicleStatusHistory::create(['vehicle_id' => $v->id, 'type' => 'status', 'to_status_id' => $s->id, 'visible_to_client' => true, 'user_id' => auth()->id(), 'created_at' => now()->subDays(40 - $i * 3)]);
                }
                for ($n = 0; $n < 3; $n++) {
                    $path = 'vehicles/'.$v->id.'/demo'.$n.'.png';
                    Storage::disk('local')->put($path, $this->carImage($rgb, $n, $make.' '.$model));
                    VehicleImage::create(['vehicle_id' => $v->id, 'path' => $path, 'is_primary' => $n === 0, 'sort_order' => $n]);
                }
                foreach ([[2000, 30], [1000, 10]] as $j => [$amt, $days]) {
                    if ($ci === 2 && $j === 1) continue;
                    Payment::create(['receipt_no' => (string) (1001 + Payment::count()), 'client_id' => $client->id, 'vehicle_id' => $v->id, 'amount' => $amt, 'paid_at' => now()->subDays($days),
                        'method' => $j ? 'bank_transfer' : 'cash', 'created_by' => auth()->id()]);
                }
            }
        }
    }

    protected function carImage(array $rgb, int $variant, string $label): string
    {
        $w = 960; $h = 640;
        $im = imagecreatetruecolor($w, $h);
        for ($y = 0; $y < $h; $y++) {
            $c = imagecolorallocate($im, 15 + (int) ($y / 20), 35 + (int) ($y / 14), 71 + (int) ($y / 9));
            imageline($im, 0, $y, $w, $y, $c);
        }
        $body = imagecolorallocate($im, ...$rgb);
        $dark = imagecolorallocate($im, 12, 18, 30);
        $glass = imagecolorallocate($im, 60, 90, 140);
        $gold = imagecolorallocate($im, 200, 162, 74);
        $ox = $variant * 30;
        imagefilledellipse($im, 480, 520, 700, 50, imagecolorallocatealpha($im, 0, 0, 0, 80));
        imagefilledrectangle($im, 150 + $ox, 330, 810 - $ox, 470, $body);
        imagefilledpolygon($im, [280 + $ox, 330, 360 + $ox, 230, 620 - $ox, 230, 700 - $ox, 330], $body);
        imagefilledpolygon($im, [305 + $ox, 325, 375 + $ox, 245, 475, 245, 475, 325], $glass);
        imagefilledpolygon($im, [490, 325, 490, 245, 605 - $ox, 245, 675 - $ox, 325], $glass);
        foreach ([290 + $ox, 670 - $ox] as $cx) {
            imagefilledellipse($im, $cx, 470, 130, 130, $dark);
            imagefilledellipse($im, $cx, 470, 70, 70, imagecolorallocate($im, 150, 155, 165));
        }
        imagefilledrectangle($im, 770 - $ox, 360, 810 - $ox, 380, $gold);
        imagestring($im, 5, 30, 30, $label.'  #'.($variant + 1), imagecolorallocate($im, 255, 255, 255));
        ob_start();
        imagepng($im);

        return ob_get_clean();
    }
}
