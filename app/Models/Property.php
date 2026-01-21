<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = ['code', 'name','property_no'];
    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }
}
