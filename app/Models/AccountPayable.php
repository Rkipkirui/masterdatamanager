<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    protected $fillable = ['account_code', 'account_name'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
