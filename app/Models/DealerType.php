<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerType extends Model
{
    protected $fillable = ['code', 'name'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
