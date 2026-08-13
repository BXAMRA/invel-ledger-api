<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceBundle extends Model
{
    protected $guarded = [];

    /**
     * @return BelongsToMany<Service, ServiceBundle>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_bundle_services');
    }
}
