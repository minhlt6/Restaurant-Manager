<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['name', 'gender', 'address', 'birthday', 'username', 'password', 'role'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['birthday' => 'date', 'role' => 'integer'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
