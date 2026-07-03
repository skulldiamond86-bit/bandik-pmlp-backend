<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $transactions = Transaction::with('category')
            ->where('user_id', auth()->id())
            ->latest('transaction_date')
            ->latest()
            ->get();

        return TransactionResource::collection($transactions);
    }

    public function store(TransactionRequest $request): TransactionResource
    {
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'amount' => $request->amount,
            'note' => $request->note,
            'transaction_date' => $request->transaction_date,
        ])->load('category');

        return new TransactionResource($transaction);
    }

    public function show(string $id): TransactionResource
    {
        $transaction = $this->findUserTransaction($id);

        return new TransactionResource($transaction);
    }

    public function update(TransactionRequest $request, string $id): TransactionResource
    {
        $transaction = $this->findUserTransaction($id);
        $transaction->update($request->only([
            'category_id',
            'title',
            'amount',
            'note',
            'transaction_date',
        ]));

        return new TransactionResource($transaction->load('category'));
    }

    public function destroy(string $id): JsonResponse
    {
        $transaction = $this->findUserTransaction($id);
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction berhasil dihapus',
        ]);
    }

    private function findUserTransaction(string $id): Transaction
    {
        return Transaction::with('category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }
}
