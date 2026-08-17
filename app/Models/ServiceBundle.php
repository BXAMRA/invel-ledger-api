<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceBundle extends Model
{
  use HasUuids, SoftDeletes;

  protected $guarded = [];

  /**
   * @return BelongsToMany<Service, ServiceBundle>
   */
  public function services(): BelongsToMany
  {
    return $this->belongsToMany(Service::class, "service_bundle_services");
  }
}
