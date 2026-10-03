<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Sppg;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'piutang'); // 'piutang' or 'riwayat'

        // Data Piutang Berjalan (Faktur Belum Lunas)
        $debtQuery = Sale::with('sppg')
            ->where('status', 'selesai')
            ->where('remaining_balance', '>', 0)
            ->latest('due_date')
            ->latest('sale_date');

        if ($sppgId = $request->input('sppg_id')) {
            $debtQuery->where('sppg_id', $sppgId);
        }

        if ($search = $request->input('search')) {
            $debtQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('sppg', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $debtSales = $debtQuery->paginate(15, ['*'], 'debt_page')->withQueryString();

        // Riwayat Pembayaran Masuk
        $paymentsQuery = Payment::with('sale.sppg')->latest('payment_date')->latest('id');

        if ($sppgId) {
            $paymentsQuery->whereHas('sale', function ($sq) use ($sppgId) {
                $sq->where('sppg_id', $sppgId);
            });
        }

        $payments = $paymentsQuery->paginate(15, ['*'], 'pay_page')->withQueryString();

        // Ringkasan Finansial Piutang
        $totalOutstandingDebt = Sale::where('status', 'selesai')->sum('remaining_balance');
        $unpaidInvoicesCount = Sale::where('status', 'selesai')->where('remaining_balance', '>', 0)->count();
        $paymentsReceivedThisMonth = Payment::whereBetween('payment_date', [Carbon::now()->startOfMonth(), Carbon::now()])->sum('amount');

        $sppgs = Sppg::where('is_active', true)->orderBy('name')->get();

        return view('payments.index', compact(
            'tab',
            'debtSales',
            'payments',
            'totalOutstandingDebt',
            'unpaidInvoicesCount',
            'paymentsReceivedThisMonth',
            'sppgs'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:tunai,transfer,lainnya',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $sale = Sale::findOrFail($validated['sale_id']);

        if ($sale->status === 'batal') {
            return back()->with('error', 'Tidak dapat mencatat pembayaran pada faktur yang telah dibatalkan.');
        }

        if ($validated['amount'] > $sale->remaining_balance) {
            return back()->with('error', 'Jumlah pembayaran tidak boleh melebihi sisa piutang (Rp ' . number_format($sale->remaining_balance, 0, ',', '.') . ').');
        }

        $payment = DB::transaction(function () use ($validated, $sale, $request) {
            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payment_proofs', 'public');
            }

            $payment = $this->paymentService->recordPayment(
                sale: $sale,
                amount: (float) $validated['amount'],
                method: $validated['payment_method'],
                reference: $validated['reference_number'] ?? null,
                notes: $validated['notes'] ?? null,
                date: $validated['payment_date'],
                proofFile: $proofPath
            );

            ActivityLog::record(
                'payment_recorded',
                Payment::class,
                $payment->id,
                "Pembayaran masuk Rp " . number_format($payment->amount, 0, ',', '.') . " untuk faktur {$sale->invoice_number} ({$sale->sppg?->name})",
                null,
                ['sale_id' => $sale->id, 'amount' => $payment->amount, 'method' => $payment->payment_method]
            );

            return $payment;
        });

        return back()->with('success', "Pembayaran sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat untuk Faktur {$sale->invoice_number}.");
    }

    public function show(Payment $payment)
    {
        $payment->load(['sale.sppg', 'sale.items.product', 'confirmedBy']);
        return view('payments.show', compact('payment'));
    }

    public function uploadProof(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $payment) {
            $proofPath = $request->file('proof_file')->store('payment_proofs', 'public');

            $payment->update([
                'proof_file' => $proofPath,
                'payment_uploaded_at' => now(),
                'confirmation_status' => 'pending',
            ]);

            ActivityLog::record(
                'payment_proof_uploaded',
                Payment::class,
                $payment->id,
                "Bukti pembayaran diunggah untuk pembayaran #{$payment->payment_number}",
                ['proof_file' => $payment->proof_file],
                ['proof_file' => $proofPath]
            );
        });

        return back()->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
    }

    public function confirmPayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'action' => 'required|in:confirm,reject',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $payment) {
            if ($validated['action'] === 'confirm') {
                $payment->update([
                    'confirmation_status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => auth()->id(),
                    'rejection_reason' => null,
                ]);

                ActivityLog::record(
                    'payment_confirmed',
                    Payment::class,
                    $payment->id,
                    "Pembayaran #{$payment->payment_number} dikonfirmasi oleh admin",
                    ['confirmation_status' => 'pending'],
                    ['confirmation_status' => 'confirmed', 'confirmed_by' => auth()->id()]
                );
            } else {
                $payment->update([
                    'confirmation_status' => 'rejected',
                    'confirmed_at' => now(),
                    'confirmed_by' => auth()->id(),
                    'rejection_reason' => $validated['rejection_reason'],
                ]);

                ActivityLog::record(
                    'payment_rejected',
                    Payment::class,
                    $payment->id,
                    "Pembayaran #{$payment->payment_number} ditolak: {$validated['rejection_reason']}",
                    ['confirmation_status' => 'pending'],
                    ['confirmation_status' => 'rejected', 'rejection_reason' => $validated['rejection_reason']]
                );
            }
        });

        return back()->with('success', $validated['action'] === 'confirm' ? 'Pembayaran berhasil dikonfirmasi.' : 'Pembayaran ditolak. Silakan minta supplier mengupload ulang bukti pembayaran.');
    }
}
