<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'deliverables' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Document, DocumentItem>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return BelongsTo<Service, DocumentItem>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
