<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\VoucherRedeem;
use App\Services\BalanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to seed data...');
        
        DB::transaction(function () {
            $balanceService = new BalanceService();

            // Step 1: Create Members (50 clients + 50 freelancers = 100 total)
            $this->command->info('Creating members...');
            $clients = Member::factory()->client()->count(10)->create();
            $freelancers = Member::factory()->freelancer()->count(10)->create();
            $this->command->info('✓ Created 20 members (10 clients + 10 freelancers)');

            // Step 2: Create Projects (100 projects)
            $this->command->info('Creating projects...');
            $projects = collect();
            foreach ($clients as $client) {
                $projectCount = rand(1, 3); // Each client gets 1-3 projects
                $clientProjects = Project::factory()->count($projectCount)->create([
                    'member' => $client->id,
                ]);
                $projects = $projects->merge($clientProjects);
            }
            $this->command->info("✓ Created {$projects->count()} projects");

            // Step 3: Create Vouchers (20 vouchers)
            $this->command->info('Creating vouchers...');
            $percentVouchers = Voucher::factory()->percent()->count(10)->create();
            $fixedVouchers = Voucher::factory()->fixed()->count(10)->create();
            $allVouchers = $percentVouchers->merge($fixedVouchers);
            $this->command->info('✓ Created 20 vouchers (10 percent + 10 fixed)');

            // Step 4: Create VoucherRedeems for some members
            $this->command->info('Creating voucher redeems...');
            $voucherRedeemCount = 0;
            foreach ($clients->random(min(30, $clients->count())) as $client) {
                $voucher = $allVouchers->random();
                $clientProjects = $projects->where('member', $client->id)->pluck('id')->toArray();
                
                if (count($clientProjects) > 0) {
                    VoucherRedeem::create([
                        'voucher' => $voucher->id,
                        'member' => $client->id,
                        'redeem' => true,
                        'projects' => implode(',', array_slice($clientProjects, 0, rand(1, count($clientProjects)))),
                    ]);
                    $voucherRedeemCount++;
                }
            }
            $this->command->info("✓ Created {$voucherRedeemCount} voucher redeems");

            // Step 5: Create Balance Operations
            $this->command->info('Creating balance operations...');
            $processCount = 0;
            $depositCount = 0;
            $withdrawalCount = 0;
            $transactionCount = 0;
            $invoiceCount = 0;

            // Create deposits (income) - 30 operations
            for ($i = 0; $i < 30; $i++) {
                try {
                    $client = $clients->random();
                    $project = $projects->where('member', $client->id)->random();
                    
                    // Check if client has voucher for this project
                    $voucherRedeem = VoucherRedeem::where('member', $client->id)
                        ->where('redeem', true)
                        ->get()
                        ->first(function ($redeem) use ($project) {
                            $projectIds = explode(',', $redeem->projects);
                            return in_array($project->id, $projectIds);
                        });
                    
                    $voucherId = $voucherRedeem ? $voucherRedeem->voucher : null;
                    
                    // Random amount between 500 and 10000
                    $amount = rand(500, 10000);
                    
                    // Payment methods
                    $paymentMethods = ['credit_card', 'debit_card', 'bank_transfer', 'mada', 'apple_pay'];
                    
                    $balance = $balanceService->createDeposit(
                        $client,
                        $project,
                        $amount,
                        [
                            'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                            'card_last4' => rand(1000, 9999),
                            'payment_status' => 'completed',
                            'payment_date' => now()->subDays(rand(1, 90))->toDateTimeString(),
                        ],
                        $voucherId
                    );
                    
                    // Update created_at to random date in the past
                    $balance->created_at = now()->subDays(rand(1, 90));
                    $balance->save();
                    
                    // Complete 80% of deposits
                    if (rand(1, 100) <= 80) {
                        $balanceService->completeBalance($balance);
                        
                        // Create invoice for 50% of completed deposits
                        if (rand(1, 100) <= 50) {
                            // Since balance_id is no longer stored in the transaction data,
                            // we approximate the matching transaction as the latest one
                            // for this client and project.
                            $transaction = Transaction::where('project', $project->id)
                                ->where('client', $client->id)
                                ->latest()
                                ->first();

                            if ($transaction) {
                                Invoice::create([
                                    'member' => $client->id,
                                    'transaction' => $transaction->id,
                                    'invoice' => 'INV-' . strtoupper(uniqid()),
                                ]);
                                $invoiceCount++;
                            }
                        }
                    }
                    
                    $depositCount++;
                    $transactionCount++; // Each deposit creates a transaction
                    $processCount++;
                    
                    if ($processCount % 10 == 0) {
                        $this->command->info("  Progress: {$processCount} operations created");
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error creating deposit: " . $e->getMessage());
                }
            }

            // Create withdrawals (outcome) - 20 operations
            // 10 for freelancers
            for ($i = 0; $i < 10; $i++) {
                try {
                    $freelancer = $freelancers->random();
                    $project = $projects->random();
                    
                    // Random amount between 500 and 5000
                    $amount = rand(500, 5000);
                    
                    $banks = ['Al Rajhi Bank', 'NCB', 'Saudi British Bank', 'Riyad Bank', 'Al Inma Bank'];
                    
                    $balance = $balanceService->createWithdrawal(
                        $freelancer,
                        $amount,
                        $project,
                        [
                            'bank_account' => 'SA' . rand(1000000000000000, 9999999999999999),
                            'bank_name' => $banks[array_rand($banks)],
                            'iban' => 'SA' . rand(1000000000000000, 9999999999999999),
                            'payout_status' => 'pending',
                        ]
                    );
                    
                    // Update created_at to random date in the past
                    $balance->created_at = now()->subDays(rand(1, 90));
                    $balance->save();
                    
                    // Complete 70% of withdrawals
                    if (rand(1, 100) <= 70) {
                        $balanceService->completeBalance($balance);
                    }
                    
                    $withdrawalCount++;
                    $processCount++;
                    
                    if ($processCount % 10 == 0) {
                        $this->command->info("  Progress: {$processCount} operations created");
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error creating freelancer withdrawal: " . $e->getMessage());
                }
            }

            // 10 for clients
            for ($i = 0; $i < 10; $i++) {
                try {
                    $client = $clients->random();
                    $project = $projects->where('member', $client->id)->random();
                    
                    // Random amount between 300 and 3000
                    $amount = rand(300, 3000);
                    
                    $banks = ['Al Rajhi Bank', 'NCB', 'Saudi British Bank', 'Riyad Bank', 'Al Inma Bank'];
                    
                    $balance = $balanceService->createWithdrawal(
                        $client,
                        $amount,
                        $project,
                        [
                            'bank_account' => 'SA' . rand(1000000000000000, 9999999999999999),
                            'bank_name' => $banks[array_rand($banks)],
                            'iban' => 'SA' . rand(1000000000000000, 9999999999999999),
                            'payout_status' => 'pending',
                        ]
                    );
                    
                    // Update created_at to random date in the past
                    $balance->created_at = now()->subDays(rand(1, 90));
                    $balance->save();
                    
                    // Complete 70% of withdrawals
                    if (rand(1, 100) <= 70) {
                        $balanceService->completeBalance($balance);
                    }
                    
                    $withdrawalCount++;
                    $processCount++;
                    
                    if ($processCount % 10 == 0) {
                        $this->command->info("  Progress: {$processCount} operations created");
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error creating client withdrawal: " . $e->getMessage());
                }
            }

            // Summary
            $this->command->info("\n=== Seeding Complete ===");
            $this->command->info("✓ Members: " . ($clients->count() + $freelancers->count()) . " ({$clients->count()} clients + {$freelancers->count()} freelancers)");
            $this->command->info("✓ Projects: {$projects->count()}");
            $this->command->info("✓ Vouchers: 20");
            $this->command->info("✓ Voucher Redeems: {$voucherRedeemCount}");
            $this->command->info("✓ Balance Operations: {$processCount}");
            $this->command->info("  - Deposits (Income): {$depositCount}");
            $this->command->info("  - Withdrawals (Outcome): {$withdrawalCount}");
            $this->command->info("✓ Transactions: {$transactionCount}");
            $this->command->info("✓ Invoices: {$invoiceCount}");
            $this->command->info("========================\n");
        });
    }
}

