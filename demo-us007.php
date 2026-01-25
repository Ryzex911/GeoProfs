<?php
// Demo script voor US007 validatie

// Maak een user aan
$user = \App\Models\User::factory()->create(['contract_fte' => 1.0, 'start_date' => '2024-01-01']);
echo "User created: {$user->name} (ID: {$user->id})\n\n";

// Maak een leave type aan
$leaveType = \App\Models\LeaveType::where('deducts_from_balance', true)->first()
    ?? \App\Models\LeaveType::factory()->create(['name' => 'Vakantie', 'deducts_from_balance' => true]);
echo "Leave type: {$leaveType->name} (deducts: {$leaveType->deducts_from_balance})\n\n";

// Test de service
$service = app(\App\Services\LeaveBalanceService::class);

// Haal startsaldo op
$startDays = $service->getStartSaldoDays($user, 2024);
echo "Startsaldo 2024: $startDays dagen\n";

// Haal resterend saldo op (nog geen aanvragen)
$balance = $service->getRemainingForUser($user, 2024);
echo "Resterend saldo: {$balance['remaining_days']} dagen ({$balance['remaining_hours']} uren)\n\n";

// Maak verlofaanvraag aan (Maandag-Dinsdag = 2 werkdagen = 16 uur)
$leave1 = \App\Models\LeaveRequest::factory()->create([
    'employee_id' => $user->id,
    'leave_type_id' => $leaveType->id,
    'start_date' => '2024-01-08',
    'end_date' => '2024-01-09',
    'status' => 'approved',
    'approved_at' => \Carbon\Carbon::create(2024, 1, 10),  // Zet approved_at in 2024!
]);

// Bereken duration_hours
$leave1->duration_hours = $service->calculateDurationHours($leave1);
$leave1->save();
echo "Leave created: {$leave1->start_date->format('Y-m-d')} tot {$leave1->end_date->format('Y-m-d')}, duration: {$leave1->duration_hours} uren\n";

// Haal saldo opnieuw op (met verlof)
$balance = $service->getRemainingForUser($user, 2024);
echo "Resterend saldo NA verlof: {$balance['remaining_days']} dagen ({$balance['remaining_hours']} uren)\n";
echo "Gebruikt: {$balance['used_days']} dagen ({$balance['used_hours']} uren)\n";
echo "\n✓ Demo completed successfully!\n";
