<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Customer extends Model
{
  use SoftDeletes, HasUuids;

  protected $guarded = [];

  /**
   * @return HasMany<Document, Customer>
   */
  public function documents(): HasMany
  {
    return $this->hasMany(Document::class);
  }
}
