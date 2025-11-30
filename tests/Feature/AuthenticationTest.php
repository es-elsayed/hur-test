<?php

use App\Models\Member;
use function Pest\Laravel\{get, post, assertAuthenticated, assertGuest};

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

test('login page can be accessed', function () {
    get('/login')
        ->assertStatus(200)
        ->assertSee('تسجيل الدخول');
});

test('user can login with valid credentials', function () {
    $member = Member::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ])
        ->assertRedirect('/balance');

    assertAuthenticated();
});

test('user cannot login with invalid credentials', function () {
    Member::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ])
        ->assertSessionHasErrors('email');

    assertGuest();
});

test('balance page requires authentication', function () {
    get('/balance')
        ->assertRedirect('/login');
});

test('authenticated user can access balance page', function () {
    $member = Member::factory()->create();

    $this->actingAs($member)
        ->get('/balance')
        ->assertStatus(200)
        ->assertSee('الرصيد');
});

test('user can logout', function () {
    $member = Member::factory()->create();

    $this->actingAs($member);
    assertAuthenticated();

    post('/logout')
        ->assertRedirect('/login');

    assertGuest();
});

test('logout button is visible when authenticated', function () {
    $member = Member::factory()->create();

    $this->actingAs($member)
        ->get('/balance')
        ->assertStatus(200)
        ->assertSee('تسجيل الخروج');
});

test('logout button is not visible when not authenticated', function () {
    get('/login')
        ->assertStatus(200)
        ->assertDontSee('تسجيل الخروج');
});
