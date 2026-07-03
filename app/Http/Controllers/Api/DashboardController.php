<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = auth()->id();

        $totalIncome = Transaction::where('transactions.user_id', $userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'income')
            ->sum('transactions.amount');

        $totalExpense = Transaction::where('transactions.user_id', $userId)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.type', 'expense')
            ->sum('transactions.amount');

        return response()->json([
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'balance' => (float) ($totalIncome - $totalExpense),
        ]);
    }
}
