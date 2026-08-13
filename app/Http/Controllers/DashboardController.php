<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
  /**
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(): JsonResponse
  {
    $customersCount = Customer::query()->count();
    $documentsCount = Document::query()->count();

    // Outstanding balance from invoices not cancelled and not draft
    $outstanding = Document::query()
      ->where("type", "invoice")
      ->whereNotIn("status", ["cancelled", "draft"])
      ->sum("balance");

    $now = Carbon::now();

    $revenueMonth = Payment::query()->whereYear("payment_date", $now->year)->whereMonth("payment_date", $now->month)->sum("amount");

    $revenueYear = Payment::query()->whereYear("payment_date", $now->year)->sum("amount");

    // Get 5 most recent documents based on created_at or due_date
    $recentDocuments = Document::query()
      ->with("customer:id,company_name")
      ->orderByRaw("due_date IS NULL ASC")
      ->orderBy("due_date", "asc")
      ->limit(5)
      ->get()
      ->map(function ($doc) {
        return [
          "id" => $doc->id,
          "document_number" => $doc->document_number,
          "customer_name" => $doc->customer ? $doc->customer->company_name : "Unknown",
          "grand_total" => $doc->grand_total,
          "due_date" => $doc->due_date,
          "status" => $doc->status,
        ];
      });

    // Get 5 most recent payments
    $recentPayments = Payment::query()
      ->with("document:id,document_number")
      ->orderBy("payment_date", "desc")
      ->limit(5)
      ->get()
      ->map(function ($payment) {
        return [
          "id" => $payment->id,
          "document_number" => $payment->document ? $payment->document->document_number : "Unknown",
          "amount" => $payment->amount,
          "payment_date" => $payment->payment_date,
          "payment_method" => $payment->payment_method,
        ];
      });

    return $this->success([
      "stats" => [
        "revenue_month" => (float) $revenueMonth,
        "revenue_year" => (float) $revenueYear,
        "outstanding" => (float) $outstanding,
        "customers_count" => $customersCount,
        "documents_count" => $documentsCount,
      ],
      "recent_documents" => $recentDocuments,
      "recent_payments" => $recentPayments,
    ]);
  }
}
