<?php

namespace Tests\Feature;

use App\Models\Balance;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalancePolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_can_create_deposit()
    {
        $client = Member::factory()->client()->create();

        $this->assertTrue($client->can('createDeposit', Balance::class));
    }

    /** @test */
    public function freelancer_cannot_create_deposit()
    {
        $freelancer = Member::factory()->freelancer()->create();

        $this->assertFalse($freelancer->can('createDeposit', Balance::class));
    }

    /** @test */
    public function client_can_create_withdrawal()
    {
        $client = Member::factory()->client()->create();

        $this->assertTrue($client->can('createWithdrawal', Balance::class));
    }

    /** @test */
    public function freelancer_can_create_withdrawal()
    {
        $freelancer = Member::factory()->freelancer()->create();

        $this->assertTrue($freelancer->can('createWithdrawal', Balance::class));
    }

    /** @test */
    public function member_can_view_own_balance()
    {
        $member = Member::factory()->create();
        $balance = Balance::factory()->create(['member' => $member->id]);

        $this->assertTrue($member->can('view', $balance));
    }

    /** @test */
    public function member_cannot_view_others_balance()
    {
        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();
        $balance = Balance::factory()->create(['member' => $member2->id]);

        $this->assertFalse($member1->can('view', $balance));
    }

    /** @test */
    public function member_can_complete_own_balance()
    {
        $member = Member::factory()->create();
        $balance = Balance::factory()->create(['member' => $member->id]);

        $this->assertTrue($member->can('complete', $balance));
    }

    /** @test */
    public function member_cannot_complete_others_balance()
    {
        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();
        $balance = Balance::factory()->create(['member' => $member2->id]);

        $this->assertFalse($member1->can('complete', $balance));
    }
}



