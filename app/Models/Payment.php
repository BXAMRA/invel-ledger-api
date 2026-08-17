<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
  use HasUuids;

  protected $guarded = [];

  protected function casts(): array
  {
    return ["payment_date" => "date"];
  }

  /**
   * @return BelongsTo<Document, Payment>
   */
  public function document(): BelongsTo
  {
    return $this->belongsTo(Document::class);
  }
}
