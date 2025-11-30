<?php

use App\Models\Balance;
use App\Models\Member;
use App\Models\Project;
use function Pest\Laravel\{getJson, postJson};

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

test('api balances requires authentication', function () {
    getJson('/api/balances')
        ->assertStatus(401);
});

test('authenticated user can get balances', function () {
    $member = Member::factory()->create();
    Balance::factory()->count(3)->create(['member' => $member->id]);

    $this->actingAs($member)
        ->getJson('/api/balances')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'data',
                'current_page',
                'last_page',
            ],
        ]);
});

test('authenticated user can get balance details', function () {
    $member = Member::factory()->create();
    $balance = Balance::factory()->create(['member' => $member->id]);

    $this->actingAs($member)
        ->getJson("/api/balances/{$balance->id}")
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'transactionRef',
                'processType',
                'processAmount',
            ],
        ]);
});

test('client can create deposit', function () {
    $client = Member::factory()->client()->create();
    $project = Project::factory()->create(['member' => $client->id]);

    $this->actingAs($client)
        ->postJson('/api/balances/deposit', [
            'project_id' => $project->id,
            'amount' => 1000.00,
            'payment_data' => [
                'payment_method' => 'credit_card',
            ],
        ])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Deposit created successfully',
        ]);

    $this->assertDatabaseHas('balances', [
        'member' => $client->id,
        'process' => 'income',
        'amount' => 1000.00,
    ]);
});

test('freelancer cannot create deposit', function () {
    $freelancer = Member::factory()->freelancer()->create();
    $project = Project::factory()->create();

    $this->actingAs($freelancer)
        ->postJson('/api/balances/deposit', [
            'project_id' => $project->id,
            'amount' => 1000.00,
        ])
        ->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});

test('client can create withdrawal', function () {
    $client = Member::factory()->client()->create();
    $project = Project::factory()->create(['member' => $client->id]);

    $this->actingAs($client)
        ->postJson('/api/balances/withdraw', [
            'amount' => 500.00,
            'project_id' => $project->id,
            'payout_data' => [
                'bank_account' => 'SA1234567890',
                'bank_name' => 'Test Bank',
            ],
        ])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Withdrawal created successfully',
        ]);

    $this->assertDatabaseHas('balances', [
        'member' => $client->id,
        'process' => 'outcome',
        'amount' => 500.00,
    ]);
});

test('freelancer can create withdrawal', function () {
    $freelancer = Member::factory()->freelancer()->create();

    $this->actingAs($freelancer)
        ->postJson('/api/balances/withdraw', [
            'amount' => 500.00,
            'payout_data' => [
                'bank_account' => 'SA1234567890',
                'bank_name' => 'Test Bank',
            ],
        ])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Withdrawal created successfully',
        ]);

    $this->assertDatabaseHas('balances', [
        'member' => $freelancer->id,
        'process' => 'outcome',
        'amount' => 500.00,
    ]);
});

test('deposit requires valid project', function () {
    $client = Member::factory()->client()->create();

    $this->actingAs($client)
        ->postJson('/api/balances/deposit', [
            'project_id' => 99999,
            'amount' => 1000.00,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('project_id');
});

test('deposit requires positive amount', function () {
    $client = Member::factory()->client()->create();
    $project = Project::factory()->create(['member' => $client->id]);

    $this->actingAs($client)
        ->postJson('/api/balances/deposit', [
            'project_id' => $project->id,
            'amount' => -100,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('amount');
});

test('withdrawal requires positive amount', function () {
    $client = Member::factory()->client()->create();

    $this->actingAs($client)
        ->postJson('/api/balances/withdraw', [
            'amount' => 0,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('amount');
});
