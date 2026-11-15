<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;

class InquiryController extends Controller
{
    public function index()
    {
        return view('admin.inquiries.index', ['items' => Inquiry::latest()->paginate(25)]);
    }

    public function toggle(Inquiry $inquiry)
    {
        $inquiry->update(['is_handled' => ! $inquiry->is_handled]);

        return back()->with('success', 'تم تحديث حالة الاستفسار.');
    }
}
