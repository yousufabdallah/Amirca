<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function admin(): User
    {
        return User::where('role', 'admin')->first();
    }

    protected function makeClient(array $over = []): Client
    {
        $this->actingAs($this->admin())->post('/admin/clients', array_merge([
            'full_name' => 'سالم بن محمد', 'phone' => '91234567', 'civil_id' => '12345678', 'create_account' => 1, 'password' => 'Client@123',
        ], $over))->assertRedirect();

        return Client::latest('id')->first();
    }

    protected function makeVehicle(Client $c, array $over = []): Vehicle
    {
        $this->actingAs($this->admin())->post('/admin/vehicles', array_merge([
            'client_id' => $c->id, 'make' => 'Toyota', 'model' => 'Camry', 'year' => 2024, 'vin' => '4T1BZ1HK0RU000001',
            'total_due' => '5000', 'status_id' => VehicleStatus::ordered()->first()->id, 'eta' => now()->addDays(5)->toDateString(),
        ], $over))->assertRedirect();

        return Vehicle::latest('id')->first();
    }

    public function test_home_and_login_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('العميد');
        $this->get('/login')->assertOk();
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/portal')->assertRedirect('/login');
    }

    public function test_admin_login_and_throttle(): void
    {
        $this->post('/admin/login', ['login' => 'admin', 'password' => 'ChangeMe@2026'])->assertRedirect('/admin');
        $this->post('/admin/logout');
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['login' => 'admin', 'password' => 'wrong']);
        }
        $this->post('/admin/login', ['login' => 'admin', 'password' => 'ChangeMe@2026'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_full_flow_client_vehicle_payment_and_portal(): void
    {
        Storage::fake('local');
        $client = $this->makeClient();
        $this->assertSame('AL-000001', $client->client_no);
        $this->assertNotSame('12345678', $client->civil_id); // مشفر
        $this->assertSame('12345678', $client->civilIdPlain());
        $this->assertNotNull($client->user_id);

        $vehicle = $this->makeVehicle($client);
        $this->assertSame('AL-CAR-000001', $vehicle->file_no);

        // رفع عدة صور
        $this->post("/admin/vehicles/{$vehicle->id}/images", ['images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')]])->assertRedirect();
        $this->assertSame(2, $vehicle->images()->count());
        $this->assertSame(1, $vehicle->images()->where('is_primary', true)->count());
        // ملف مزيف بامتداد صورة يُرفض
        $this->post("/admin/vehicles/{$vehicle->id}/images", ['images' => [UploadedFile::fake()->createWithContent('x.jpg', '<?php echo 1;')]])->assertSessionHasErrors();

        // تحديث الحالة
        $shipping = VehicleStatus::where('stage_group', 'shipping')->first();
        $this->postJson("/admin/vehicles/{$vehicle->id}/status", ['status_id' => $shipping->id, 'note' => 'تم الشحن', 'visible_to_client' => 1])->assertOk();
        $this->assertSame($shipping->id, $vehicle->fresh()->status_id);
        $this->assertDatabaseHas('vehicle_status_history', ['vehicle_id' => $vehicle->id, 'to_status_id' => $shipping->id, 'user_id' => $this->admin()->id]);

        // دفعتان وحساب المتبقي تلقائيًا
        $this->post('/admin/payments', ['client_id' => $client->id, 'vehicle_id' => $vehicle->id, 'amount' => '2000', 'paid_at' => now()->toDateString(), 'method' => 'cash'])->assertRedirect();
        $this->post('/admin/payments', ['client_id' => $client->id, 'vehicle_id' => $vehicle->id, 'amount' => '1000.500', 'paid_at' => now()->toDateString(), 'method' => 'bank_transfer'])->assertRedirect();
        $fin = $vehicle->fresh()->financials();
        $this->assertSame('3000.500', $fin['paid']);
        $this->assertSame('1999.500', $fin['remaining']);
        $this->assertSame('partial', $fin['status']);
        $this->assertSame(['1001', '1002'], Payment::orderBy('id')->pluck('receipt_no')->all());

        // الإيصال PDF
        $p = Payment::first();
        $res = $this->get("/admin/payments/{$p->id}/receipt");
        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('Content-Type'));

        // إلغاء دفعة (لا حذف)
        $this->post("/admin/payments/{$p->id}/cancel", ['cancel_reason' => 'خطأ في الإدخال'])->assertRedirect();
        $this->assertSame('cancelled', $p->fresh()->status);
        $this->assertSame('1000.500', $vehicle->fresh()->financials()['paid']);

        // كشف الحساب Excel و PDF
        $this->get("/admin/clients/{$client->id}/statement?format=excel")->assertOk();
        $this->get("/admin/clients/{$client->id}/statement?format=pdf")->assertOk();
        $this->get("/admin/clients/{$client->id}/statement")->assertOk()->assertSee($client->full_name);

        // التقارير
        foreach (array_keys(\App\Http\Controllers\Admin\ReportController::REPORTS) as $type) {
            $this->get("/admin/reports/{$type}")->assertOk();
        }
        $this->get('/admin/reports/payments?format=excel')->assertOk();
        $this->get('/admin/reports/vehicles?format=pdf')->assertOk();

        // صفحات الإدارة الأساسية
        foreach (['/admin', '/admin/clients', "/admin/clients/{$client->id}", '/admin/vehicles', "/admin/vehicles/{$vehicle->id}", "/admin/vehicles/{$vehicle->id}/edit",
            '/admin/vehicles/create', '/admin/arrivals', '/admin/arrivals?bucket=late', '/admin/statuses', '/admin/payments', '/admin/payments/create', "/admin/payments/{$p->id}",
            '/admin/statements', '/admin/expenses', '/admin/reports', '/admin/notifications', '/admin/users', '/admin/users/create', '/admin/settings', '/admin/backups', '/admin/audit',
            '/admin/inquiries', '/admin/account', "/admin/vehicles/{$vehicle->id}/print", '/admin/search?q=Toyota', '/admin/stats', '/admin/notifications/count'] as $url) {
            $this->get($url)->assertOk();
        }

        // بوابة العميل
        $this->post('/admin/logout');
        $this->post('/login', ['login' => '91234567', 'password' => 'Client@123'])->assertRedirect('/portal');
        foreach (['/portal', '/portal/vehicles', "/portal/vehicles/{$vehicle->id}", '/portal/finance', '/portal/payments', '/portal/documents', '/portal/updates', '/portal/profile', '/portal/statement', '/portal/stats'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get("/portal/vehicles/{$vehicle->id}")->assertSee('Camry')->assertSee('تم الشحن');
        $this->get('/portal/stats')->assertJsonPath('remaining', money('3999.500'));
        $this->get('/portal/statement?format=pdf')->assertOk();
        // الدفعة الملغاة لا تظهر للعميل
        $this->get("/portal/payments/{$p->id}/receipt")->assertNotFound();
        $active = Payment::where('status', 'active')->first();
        $this->get("/portal/payments/{$active->id}/receipt")->assertOk();
        $img = $vehicle->images()->first();
        $this->get("/portal/images/{$img->id}")->assertOk();
        // العميل لا يدخل لوحة الإدارة
        $this->get('/admin')->assertRedirect('/portal');
        $this->get('/admin/clients')->assertRedirect('/portal');
    }

    public function test_client_cannot_access_other_clients_data(): void
    {
        Storage::fake('local');
        $a = $this->makeClient();
        $va = $this->makeVehicle($a);
        $b = $this->makeClient(['full_name' => 'خالد', 'phone' => '99887766', 'civil_id' => '87654321', 'password' => 'Other@123']);
        $vb = $this->makeVehicle($b, ['vin' => 'X2']);
        $this->post("/admin/vehicles/{$vb->id}/images", ['images' => [UploadedFile::fake()->image('b.jpg')]]);
        $this->post("/admin/vehicles/{$vb->id}/documents", ['title' => 'فاتورة', 'type' => 'purchase_invoice', 'visible_to_client' => 1,
            'file' => UploadedFile::fake()->createWithContent('f.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF")])->assertRedirect();
        $this->post('/admin/payments', ['client_id' => $b->id, 'vehicle_id' => $vb->id, 'amount' => '100', 'paid_at' => now()->toDateString(), 'method' => 'cash']);
        $pay = Payment::latest('id')->first();
        $doc = $vb->documents()->first();
        $img = $vb->images()->first();
        // دفعة على سيارة لا تتبع العميل تُرفض
        $this->post('/admin/payments', ['client_id' => $a->id, 'vehicle_id' => $vb->id, 'amount' => '100', 'paid_at' => now()->toDateString(), 'method' => 'cash'])->assertSessionHasErrors('vehicle_id');
        $this->post('/admin/logout');

        $this->post('/login', ['login' => '91234567', 'password' => 'Client@123'])->assertRedirect();
        $this->get("/portal/vehicles/{$va->id}")->assertOk();
        $this->get("/portal/vehicles/{$vb->id}")->assertNotFound();
        $this->get("/portal/images/{$img->id}")->assertNotFound();
        $this->get("/portal/documents/{$doc->id}")->assertNotFound();
        $this->get("/portal/payments/{$pay->id}/receipt")->assertNotFound();
        $this->get("/portal/statement?vehicle={$vb->id}")->assertNotFound();
        $this->get('/portal/vehicles')->assertDontSee('X2');
    }

    public function test_staff_permissions_enforced_on_server(): void
    {
        $staff = User::create(['name' => 'موظف', 'username' => 'staff1', 'password' => 'Staff@1234', 'role' => 'staff', 'is_active' => true,
            'permissions' => ['clients.view', 'vehicles.view']]);
        $client = $this->makeClient();
        $this->actingAs($staff);
        $this->get('/admin/clients')->assertOk()->assertDontSee('إجمالي المستحق');
        $this->get('/admin/payments')->assertForbidden();
        $this->post('/admin/payments', [])->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->post('/admin/clients', ['full_name' => 'x', 'phone' => '12345678'])->assertForbidden();
        $this->get('/admin/reports')->assertForbidden();
        $this->get("/admin/clients/{$client->id}/statement")->assertForbidden();

        // المدير يمنح صلاحيات
        $this->actingAs($this->admin())->put("/admin/users/{$staff->id}", ['name' => 'موظف', 'username' => 'staff1', 'role' => 'staff', 'is_active' => 1,
            'permissions' => ['clients.view', 'vehicles.view', 'payments.view']])->assertRedirect();
        $this->actingAs($staff->fresh())->get('/admin/payments')->assertOk();
    }

    public function test_status_management_and_arrival_alerts(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/statuses', ['name' => 'فحص إضافي', 'color' => '#123456', 'stage_group' => 'purchased', 'is_active' => 1])->assertRedirect();
        $new = VehicleStatus::where('name', 'فحص إضافي')->first();
        $this->delete("/admin/statuses/{$new->id}")->assertRedirect();
        $this->assertModelMissing($new);

        $client = $this->makeClient();
        $v = $this->makeVehicle($client, ['eta' => now()->subDays(3)->toDateString()]);
        $before = $v->status_id;
        $this->assertSame(1, \App\Support\ArrivalAlerts::run());
        $this->assertSame($before, $v->fresh()->status_id); // لا تتغير الحالة تلقائيًا
        $this->assertDatabaseHas('notifications', ['type' => 'arrival.late']);
        $this->get('/admin/arrivals?bucket=late')->assertOk()->assertSee($v->file_no);
        // مرحلة مستخدمة لا تحذف
        $this->delete("/admin/statuses/{$before}")->assertSessionHas('error');
    }

    public function test_backup_runs(): void
    {
        $this->actingAs($this->admin())->post('/admin/backups')->assertRedirect()->assertSessionHas('success');
        $this->assertNotEmpty(\App\Support\Backup::list());
        foreach (\App\Support\Backup::list() as $f) {
            Storage::disk('local')->delete('backups/'.$f['name']);
        }
    }

    public function test_default_admin_password_must_be_changed(): void
    {
        $this->post('/admin/login', ['login' => 'admin', 'password' => 'ChangeMe@2026'])->assertRedirect('/admin');
        $this->get('/admin')->assertRedirect('/admin/account');
        $this->get('/admin/clients')->assertRedirect('/admin/account');
        $this->put('/admin/account/password', ['current_password' => 'ChangeMe@2026', 'password' => 'ChangeMe@2026', 'password_confirmation' => 'ChangeMe@2026'])->assertSessionHasErrors('password');
        $this->put('/admin/account/password', ['current_password' => 'ChangeMe@2026', 'password' => 'NewStrong123', 'password_confirmation' => 'NewStrong123'])->assertSessionHas('success');
        $this->get('/admin')->assertOk();
    }

    public function test_notification_links_cannot_redirect_offsite(): void
    {
        $admin = $this->admin();
        foreach (['http://localhost.evil.com/x', 'https://evil.com', '//evil.com'] as $bad) {
            $n = \App\Models\AppNotification::create(['user_id' => $admin->id, 'type' => 't', 'title' => 't', 'url' => $bad]);
            $this->actingAs($admin)->get("/admin/notifications/{$n->id}")->assertRedirect(route('admin.notifications.index'));
        }
        $ok = \App\Models\AppNotification::create(['user_id' => $admin->id, 'type' => 't', 'title' => 't', 'url' => url('/admin/clients')]);
        $this->get("/admin/notifications/{$ok->id}")->assertRedirect(url('/admin/clients'));
    }
}
