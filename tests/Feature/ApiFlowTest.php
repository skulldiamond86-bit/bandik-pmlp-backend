<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_finance_data_with_jwt(): void
    {
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password123',
        ]);

        $registerResponse->assertCreated()
            ->assertJsonStructure(['message', 'user', 'token', 'token_type']);

        $token = $registerResponse->json('token');

        $incomeResponse = $this->withToken($token)->postJson('/api/categories', [
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        $incomeResponse->assertCreated()
            ->assertJsonPath('data.name', 'Gaji')
            ->assertJsonPath('data.type', 'income');

        $expenseResponse = $this->withToken($token)->postJson('/api/categories', [
            'name' => 'Makan',
            'type' => 'expense',
        ]);

        $expenseResponse->assertCreated()
            ->assertJsonPath('data.name', 'Makan')
            ->assertJsonPath('data.type', 'expense');

        $this->withToken($token)->postJson('/api/transactions', [
            'category_id' => $incomeResponse->json('data.id'),
            'title' => 'Gaji Bulanan',
            'amount' => 5000000,
            'note' => 'Juli',
            'transaction_date' => '2026-07-03',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Gaji Bulanan')
            ->assertJsonPath('data.amount', 5000000);

        $this->withToken($token)->postJson('/api/transactions', [
            'category_id' => $expenseResponse->json('data.id'),
            'title' => 'Makan Siang',
            'amount' => 25000,
            'note' => null,
            'transaction_date' => '2026-07-03',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Makan Siang')
            ->assertJsonPath('data.amount', 25000);

        $this->withToken($token)->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('total_income', 5000000)
            ->assertJsonPath('total_expense', 25000)
            ->assertJsonPath('balance', 4975000);

        $this->withToken($token)->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil');
    }
}
