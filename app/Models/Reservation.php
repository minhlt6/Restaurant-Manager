<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    public const STATUS_WAITING  = 0;
    public const STATUS_RECEIVED = 1;
    public const STATUS_CANCELLED = 2;

    protected $fillable = [
        'dining_table_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'reservation_time',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reservation_time' => 'datetime',
            'status'           => 'integer',
        ];
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
