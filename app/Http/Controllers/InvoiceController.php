<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TimeEntry;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->invoices()
            ->with('client', 'items')
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'status' => 'in:draft,sent,paid,overdue',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $validated['user_id'] = $request->user()->id;

        $invoice = Invoice::create($validated);

        return response()->json($invoice->load('client'), 201);
    }

    public function show(Invoice $invoice)
    {
        return $invoice->load('client', 'items.timeEntry.project');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'issue_date' => 'date',
            'due_date' => 'date|after:issue_date',
            'status' => 'in:draft,sent,paid,overdue',
            'total_amount' => 'numeric|min:0'
        ]);

        $invoice->update($validated);

        return response()->json($invoice->load('client'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'time_entry_ids' => 'required|array',
            'time_entry_ids.*' => 'exists:time_entries,id',
            'due_days' => 'integer|min:1'
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $timeEntries = TimeEntry::whereIn('id', $validated['time_entry_ids'])
            ->whereNotNull('stopped_at')
            ->with('project')
            ->get();

        if ($timeEntries->isEmpty()) {
            return response()->json(['message' => 'No valid time entries found'], 400);
        }

        return DB::transaction(function () use ($request, $client, $timeEntries, $validated) {
            $totalAmount = 0;

            $invoice = Invoice::create([
                'client_id' => $client->id,
                'user_id' => $request->user()->id,
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays($validated['due_days'] ?? 30),
                'status' => 'draft',
                'total_amount' => 0
            ]);

            foreach ($timeEntries as $entry) {
                $hours = $entry->duration_minutes / 60;
                $amount = $hours * $client->hourly_rate;
                $totalAmount += $amount;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'time_entry_id' => $entry->id,
                    'description' => $entry->description ?? $entry->project->name,
                    'hours' => $hours,
                    'rate' => $client->hourly_rate,
                    'amount' => $amount
                ]);
            }

            $invoice->update(['total_amount' => $totalAmount]);

            return response()->json($invoice->load('client', 'items'), 201);
        });
    }
}
