<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
  use HasUuids, SoftDeletes;

  protected $guarded = [];

  protected function casts(): array
  {
    return [
      "issue_date" => "date:Y-m-d",
      "due_date" => "date:Y-m-d",
      "attachments" => "array",
    ];
  }

  protected function serializeDate(\DateTimeInterface $date): string
  {
    return Carbon::instance($date)->setTimezone(config("app.timezone"))->format("Y-m-d H:i:s");
  }

  /**
   * @return BelongsTo<Customer, Document>
   */
  public function customer(): BelongsTo
  {
    return $this->belongsTo(Customer::class);
  }

  /**
   * @return HasMany<DocumentItem, Document>
   */
  public function items(): HasMany
  {
    return $this->hasMany(DocumentItem::class);
  }

  /**
   * @return HasMany<Payment, Document>
   */
  public function payments(): HasMany
  {
    return $this->hasMany(Payment::class);
  }
}
