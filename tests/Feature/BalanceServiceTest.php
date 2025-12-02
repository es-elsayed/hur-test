<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Project;
use App\Models\Voucher;
use App\Models\VoucherRedeem;
use App\Repositories\BalanceRepository;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = new BalanceService(new BalanceRepository());
    }

    #[Test]
    public function it_calculates_deposit_fees_for_client_correctly()
    {
        $client = Member::factory()->client()->create();
        $project = Project::factory()->create(['member' => $client->id]);

        $fees = $this->balanceService->calculateDepositFees($client, 1000, $project->id);

        $this->assertEquals(1000, $fees['base_amount']);
        $this->assertEquals(80, $fees['commission_amount']); // 8% of 1000
        $this->assertEquals(12, $fees['vat_amount']); // 15% of 80
        $this->assertEquals(0, $fees['discount_amount']);
        $this->assertEquals(1092, $fees['total_amount']); // 1000 + 80 + 12
    }

    #[Test]
    public function it_calculates_withdrawal_fees_for_client_correctly()
    {
        $client = Member::factory()->client()->create();

        $fees = $this->balanceService->calculateWithdrawalFees($client, 1000);

        $this->assertEquals(1000, $fees['base_amount']);
        $this->assertEquals(80, $fees['commission_amount']); // 8% of 1000
        $this->assertEquals(12, $fees['vat_amount']); // 15% of 80
        $this->assertEquals(908, $fees['net_payout']); // 1000 - 80 - 12
    }

    #[Test]
    public function it_calculates_withdrawal_fees_for_freelancer_correctly()
    {
        $freelancer = Member::factory()->freelancer()->create();

        $fees = $this->balanceService->calculateWithdrawalFees($freelancer, 1000);

        $this->assertEquals(1000, $fees['base_amount']);
        $this->assertEquals(150, $fees['commission_amount']); // 15% of 1000
        $this->assertEquals(22.5, $fees['vat_amount']); // 15% of 150
        $this->assertEquals(827.5, $fees['net_payout']); // 1000 - 150 - 22.5
    }

    #[Test]
    public function it_applies_percent_voucher_discount()
    {
        $client = Member::factory()->client()->create();
        $project = Project::factory()->create(['member' => $client->id]);
        $voucher = Voucher::factory()->percent()->create(['discount_value' => 10]);
        
        VoucherRedeem::create([
            'voucher' => $voucher->id,
            'member' => $client->id,
            'redeem' => true,
            'projects' => (string)$project->id,
        ]);

        $fees = $this->balanceService->calculateDepositFees($client, 1000, $project->id, $voucher->id);

        $this->assertEquals(100, $fees['discount_amount']); // 10% of 1000
        $this->assertEquals(992, $fees['total_amount']); // 1000 + 80 + 12 - 100
    }

    #[Test]
    public function it_applies_fixed_voucher_discount()
    {
        $client = Member::factory()->client()->create();
        $project = Project::factory()->create(['member' => $client->id]);
        $voucher = Voucher::factory()->fixed()->create(['discount_value' => 50]);
        
        VoucherRedeem::create([
            'voucher' => $voucher->id,
            'member' => $client->id,
            'redeem' => true,
            'projects' => (string)$project->id,
        ]);

        $fees = $this->balanceService->calculateDepositFees($client, 1000, $project->id, $voucher->id);

        $this->assertEquals(50, $fees['discount_amount']);
        $this->assertEquals(1042, $fees['total_amount']); // 1000 + 80 + 12 - 50
    }

    #[Test]
    public function it_creates_deposit_for_client()
    {
        $client = Member::factory()->client()->create();
        $project = Project::factory()->create(['member' => $client->id]);

        $balance = $this->balanceService->createDeposit($client, $project, 1000);

        $this->assertDatabaseHas('balances', [
            'id' => $balance->id,
            'member' => $client->id,
            'process' => 'income',
            'amount' => 1000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'project' => $project->id,
            'client' => $client->id,
        ]);
    }

    #[Test]
    public function it_throws_exception_when_freelancer_tries_to_deposit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only clients can make deposits');

        $freelancer = Member::factory()->freelancer()->create();
        $project = Project::factory()->create();

        $this->balanceService->createDeposit($freelancer, $project, 1000);
    }

    #[Test]
    public function it_creates_withdrawal_for_client()
    {
        $client = Member::factory()->client()->create();
        $project = Project::factory()->create(['member' => $client->id]);

        $balance = $this->balanceService->createWithdrawal($client, 500, $project);

        $this->assertDatabaseHas('balances', [
            'id' => $balance->id,
            'member' => $client->id,
            'process' => 'outcome',
            'amount' => 500,
        ]);

        // Withdrawals don't create transactions
        $this->assertDatabaseMissing('transactions', [
            'client' => $client->id,
        ]);
    }

    #[Test]
    public function it_creates_withdrawal_for_freelancer()
    {
        $freelancer = Member::factory()->freelancer()->create();

        $balance = $this->balanceService->createWithdrawal($freelancer, 500);

        $this->assertDatabaseHas('balances', [
            'id' => $balance->id,
            'member' => $freelancer->id,
            'process' => 'outcome',
            'amount' => 500,
        ]);
    }
}



