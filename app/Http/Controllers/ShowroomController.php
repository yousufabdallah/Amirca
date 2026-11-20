<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Services\FileStore;
use Illuminate\Http\Request;

/** معرض السيارات العام: سيارات المكتب غير المرتبطة بعميل — بدون أي بيانات داخلية أو تكاليف */
class ShowroomController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->validate([
            'q' => 'nullable|string|max:80', 'make' => 'nullable|string|max:80', 'year' => 'nullable|integer',
            'sort' => 'nullable|in:new,price_asc,price_desc,year',
        ]);
        $q = trim((string) ($f['q'] ?? ''));
        $like = '%'.addcslashes($q, '%_\\').'%';
        $base = Vehicle::publicListed();

        $vehicles = (clone $base)->with(['status', 'primaryImage'])->withCount('images')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->whereRaw("CONCAT(make,' ',model) LIKE ?", [$like])->orWhere('trim', 'like', $like)->orWhere('file_no', 'like', $like)))
            ->when(! empty($f['make']), fn ($w) => $w->where('make', $f['make']))
            ->when(! empty($f['year']), fn ($w) => $w->where('year', $f['year']))
            ->when(($f['sort'] ?? 'new') === 'price_asc', fn ($w) => $w->orderByRaw('sale_price IS NULL')->orderBy('sale_price'))
            ->when(($f['sort'] ?? '') === 'price_desc', fn ($w) => $w->orderByDesc('sale_price'))
            ->when(($f['sort'] ?? '') === 'year', fn ($w) => $w->orderByDesc('year'))
            ->latest('id')->paginate(12)->withQueryString();

        return view('site.showroom.index', [
            'vehicles' => $vehicles,
            'f' => $f,
            'makes' => (clone $base)->distinct()->orderBy('make')->pluck('make'),
            'years' => (clone $base)->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'total' => (clone $base)->count(),
        ]);
    }

    public function show(string $fileNo)
    {
        $vehicle = Vehicle::publicListed()->where('file_no', $fileNo)->with(['status', 'images'])->firstOrFail();

        return view('site.showroom.show', [
            'vehicle' => $vehicle,
            'related' => Vehicle::publicListed()->where('id', '<>', $vehicle->id)->with(['status', 'primaryImage'])
                ->orderByRaw('make = ? DESC', [$vehicle->make])->latest('id')->limit(3)->get(),
        ]);
    }

    public function image(string $fileNo, VehicleImage $image)
    {
        $vehicle = $image->vehicle;
        abort_unless($vehicle && $vehicle->file_no === $fileNo && $vehicle->isPublic(), 404);

        return FileStore::response($image->path)->setPublic()->setMaxAge(86400);
    }

    public static function imageUrl(Vehicle $v, ?VehicleImage $img): ?string
    {
        return $img ? route('showroom.image', [$v->file_no, $img->id]) : null;
    }
}
