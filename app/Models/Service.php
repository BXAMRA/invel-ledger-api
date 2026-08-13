<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'default_deliverables' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<ServiceBundle, Service>
     */
    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(ServiceBundle::class, 'service_bundle_services');
    }
}
