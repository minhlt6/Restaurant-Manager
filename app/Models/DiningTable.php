<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiningTable extends Model
{
    public const STATUS_FREE = 0;
    public const STATUS_SERVING = 1;
    public const STATUS_RESERVED = 2;

    protected $fillable = ['name', 'capacity', 'status'];

    protected function casts(): array
    {
        return ['capacity' => 'integer', 'status' => 'integer'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function extraOrders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_dining_tables');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
