<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Services\InvoiceGenerationService;
use App\Http\Requests\StoreInvoiceRequest;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceGenerationService $invoiceService
    ) {}

    public function index(Request $request)
    {
        return $request->user()
            ->invoices()
            ->with('client', 'items')
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = Invoice::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

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

        try {
            $invoice = $this->invoiceService->generateFromTimeEntries(
                $client,
                $request->user(),
                $validated['time_entry_ids'],
                $validated['due_days'] ?? 30
            );
            return response()->json($invoice, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
