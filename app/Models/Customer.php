<?php

namespace App\Models;

use App\Models\PriceList;
use App\Models\Series;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'branch_id',
        'series_id',
        'code',
        'name',
        'group_id',
        'currency_id',
        'pin',
        'tel1',
        'tel2',
        'mobile',
        'email',
        'contact_id',
        'id_staff_no_2',
        'address_id',
        'po_box',
        'city',
        'country_id',
        'payment_term_id',
        'price_list_id',
        'account_payable_id',
        'dealer_category_id',
        'dealer_type_id',
        'territory_id',
        'dealer_discount'
    ];

    // ===== Master Data Relationships =====

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class);
    }
    public function dealerCategory()
    {
        return $this->belongsTo(DealerCategory::class);
    }


    public function dealerType()
    {
        return $this->belongsTo(DealerType::class, 'dealer_type_id');
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }


    public function properties()
    {
        return $this->belongsToMany(Property::class, 'customer_property');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Customer.php
    public function customerSeries()
    {
        return $this->belongsTo(CustomerSeries::class, 'series_id');
    }
}
