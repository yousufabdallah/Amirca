<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->only(['category', 'from', 'to']);
        $query = Expense::query()
            ->when(! empty($f['category']), fn ($w) => $w->where('category', $f['category']))
            ->when(! empty($f['from']), fn ($w) => $w->where('expense_date', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($w) => $w->where('expense_date', '<=', $f['to']));
        $total = (string) (clone $query)->sum('amount');
        $expenses = $query->with(['vehicle:id,file_no,make,model', 'creator:id,name'])->latest('expense_date')->latest('id')->paginate(25)->withQueryString();

        return view('admin.payments.expenses', ['expenses' => $expenses, 'f' => $f, 'total' => $total, 'categories' => Expense::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $e = Expense::create($this->validated($request) + ['created_by' => $request->user()->id]);
        AuditLog::record('expense.create', 'تسجيل مصروف '.money($e->amount).' ('.$e->category.'): '.$e->description, $e);

        return back()->with('success', 'تم تسجيل المصروف.');
    }

    public function update(Request $request, Expense $expense)
    {
        $old = (string) $expense->amount;
        $expense->update($this->validated($request));
        AuditLog::record('expense.update', 'تعديل مصروف: '.$expense->description.' من '.money($old).' إلى '.money($expense->amount), $expense);

        return back()->with('success', 'تم تعديل المصروف.');
    }

    public function destroy(Expense $expense)
    {
        AuditLog::record('expense.delete', 'حذف مصروف '.money($expense->amount).': '.$expense->description, $expense, $expense->toArray());
        $expense->delete();

        return back()->with('success', 'تم حذف المصروف.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|gt:0|max:99999999999',
            'expense_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ], [], ['category' => 'التصنيف', 'description' => 'الوصف', 'amount' => 'المبلغ', 'expense_date' => 'التاريخ']);
    }
}
