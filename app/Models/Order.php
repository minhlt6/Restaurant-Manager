<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_UNPAID = 0;
    public const STATUS_PAID = 1;
    public const STATUS_CANCELLED = 2;

    protected $fillable = [
        'dining_table_id',
        'employee_id',
        'customer_id',
        'total_price',
        'status',
        'time_in',
        'time_out',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'status' => 'integer',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
        ];
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
