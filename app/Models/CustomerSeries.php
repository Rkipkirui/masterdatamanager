<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSeries extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'series',        // SAP series code (e.g., 960)
        'series_name',   // SAP series name (e.g., C-MSA-M)
        'next_number',   // Next available card code number
    ];

    /**
     * Generate the next CardCode for this series and increment next_number
     *
     * @return string
     */
    public function getNextCardCode(): string
    {
        $cardCode = $this->series_name . '-' . str_pad($this->next_number, 5, '0', STR_PAD_LEFT);

        // Increment the next number
        $this->increment('next_number');

        return $cardCode;
    }
}
