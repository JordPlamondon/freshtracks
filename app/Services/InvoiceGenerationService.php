<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TimeEntry;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationService
{
    public function generateFromTimeEntries(
        Client $client,
        User $user,
        array $timeEntryIds,
        int $dueDays = 30
    ): Invoice {
        $timeEntries = TimeEntry::whereIn('id', $timeEntryIds)
            ->whereNotNull('stopped_at')
            ->with('project')
            ->get();

        if ($timeEntries->isEmpty()) {
            throw new \InvalidArgumentException('No valid time entries found');
        }

        return DB::transaction(function () use ($client, $user, $timeEntries, $dueDays) {
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'user_id' => $user->id,
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays($dueDays),
                'status' => 'draft',
                'total_amount' => 0,
            ]);

            $lineItems = $this->calculateLineItems($timeEntries, $client->hourly_rate);
            $totalAmount = 0;

            foreach ($lineItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'time_entry_id' => $item['time_entry_id'],
                    'description' => $item['description'],
                    'hours' => $item['hours'],
                    'rate' => $item['rate'],
                    'amount' => $item['amount'],
                ]);
                $totalAmount += $item['amount'];
            }

            $invoice->update(['total_amount' => $totalAmount]);

            return $invoice->fresh(['client', 'items']);
        });
    }

    public function calculateLineItems(Collection $timeEntries, float $hourlyRate): array
    {
        return $timeEntries->map(function ($entry) use ($hourlyRate) {
            $hours = $entry->duration_minutes / 60;
            $amount = $hours * $hourlyRate;

            return [
                'time_entry_id' => $entry->id,
                'description' => $entry->description ?? $entry->project->name,
                'hours' => $hours,
                'rate' => $hourlyRate,
                'amount' => $amount,
            ];
        })->all();
    }
}
