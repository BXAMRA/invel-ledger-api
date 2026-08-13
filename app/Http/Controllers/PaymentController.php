<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\PaymentLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\PaymentReceiptMail;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
  /**
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $query = Payment::with("document.customer");

    if ($request->has("document_id")) {
      $query->where("document_id", $request->query("document_id"));
    }

    if ($request->has("search")) {
      $search = $request->query("search");
      $query->where(function ($q) use ($search) {
        $q->where("reference_number", "like", "%{$search}%")->orWhereHas("document", function ($docQ) use ($search) {
          $docQ->where("document_number", "like", "%{$search}%")->orWhereHas("customer", function ($custQ) use ($search) {
            $custQ->where("company_name", "like", "%{$search}%");
          });
        });
      });
    }

    $payments = $query->latest()->paginate($request->query("per_page", 15));

    return $this->success($payments);
  }

  /**
   * @param \App\Http\Requests\StorePaymentRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(StorePaymentRequest $request): JsonResponse
  {
    $validated = $request->validated();

    /** @var \App\Models\Payment $payment */
    $payment = DB::transaction(function () use ($validated) {
      $pay = Payment::query()->create($validated);

      $doc = Document::query()->find($validated["document_id"]);
      $doc->paid_total += $pay->amount;
      $doc->balance = $doc->grand_total - $doc->paid_total;

      if ($doc->balance <= 0) {
        $doc->status = "paid";
      } elseif ($doc->paid_total > 0) {
        $doc->status = "partially_paid";
      }
      $doc->save();

      return $pay;
    });

    $payment->load("document.customer");

    // Send Payment Receipt Email
    $doc = $payment->document;
    $customer = $doc->customer;
    $email = $customer->contact_email ?: $customer->email;

    if ($email) {
      $settings = Setting::query()->pluck("value", "key")->toArray();

      $wallets = isset($settings["company.mobileWallets"]) ? json_decode($settings["company.mobileWallets"], true) : [];
      $primaryWallet = null;
      if (is_array($wallets)) {
        foreach ($wallets as $w) {
          if (empty($w["_deleted"])) {
            $primaryWallet = $w;
            break;
          }
        }
      }

      $otherPendingInvoices = Document::query()
        ->where("customer_id", $customer->id)
        ->where("type", "invoice")
        ->where("id", "!=", $doc->id)
        ->where("balance", ">", 0)
        ->whereNotIn("status", ["draft", "cancelled", "paid"])
        ->get()
        ->map(function ($d) use ($primaryWallet) {
          $link = null;
          if ($primaryWallet) {
            $link = PaymentLinkService::generate($primaryWallet["provider"], $primaryWallet["value"], $d->balance, $d->document_number);
          }
          return [
            "invoice_number" => $d->document_number,
            "total" => $d->grand_total,
            "pending" => $d->balance,
            "due" => $d->due_date,
            "link" => $link,
          ];
        })
        ->all();

      $mail = new PaymentReceiptMail($doc, $payment, $otherPendingInvoices, $settings);

      $targetEmail = app()->environment("local") ? env("MAIL_TEST_EMAIL", $email) : $email;
      Mail::to($targetEmail)->later(now()->addMinutes(2), $mail);
    }

    return $this->success($payment, "Payment recorded successfully", 201);
  }

  /**
   * @param \App\Models\Payment $payment
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(Payment $payment): JsonResponse
  {
    $payment->load("document.customer");

    return $this->success($payment);
  }

  /**
   * @param \App\Http\Requests\UpdatePaymentRequest $request
   * @param \App\Models\Payment $payment
   * @return \Illuminate\Http\JsonResponse
   */
  public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
  {
    $validated = $request->validated();

    DB::transaction(function () use ($validated, $payment) {
      $oldAmount = $payment->amount;
      $payment->update($validated);

      if (isset($validated["amount"]) && $validated["amount"] != $oldAmount) {
        $diff = $validated["amount"] - $oldAmount;
        $doc = $payment->document;
        $doc->paid_total += $diff;
        $doc->balance = $doc->grand_total - $doc->paid_total;

        if ($doc->balance <= 0) {
          $doc->status = "paid";
        } elseif ($doc->paid_total > 0) {
          $doc->status = "partially_paid";
        } else {
          $doc->status = "sent"; // Fallback if no payments left
        }

        $doc->save();
      }
    });

    $payment->load("document.customer");

    return $this->success($payment, "Payment updated successfully");
  }

  /**
   * @param \App\Models\Payment $payment
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy(Payment $payment): JsonResponse
  {
    DB::transaction(function () use ($payment) {
      $doc = $payment->document;
      $doc->paid_total -= $payment->amount;
      if ($doc->paid_total < 0) {
        $doc->paid_total = 0;
      }
      $doc->balance = $doc->grand_total - $doc->paid_total;

      if ($doc->balance <= 0) {
        $doc->status = "paid";
      } elseif ($doc->paid_total > 0) {
        $doc->status = "partially_paid";
      } else {
        $doc->status = "sent"; // Fallback if no payments left
      }

      $doc->save();

      $payment->delete();
    });

    return $this->success(null, "Payment deleted successfully");
  }
}
