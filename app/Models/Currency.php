<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
