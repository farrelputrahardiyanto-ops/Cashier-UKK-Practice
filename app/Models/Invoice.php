<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'user_id',
        'customer_id',
        'total'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice_detail()
    {
        return $this->hasMany(Invoice_Detail::class, 'invoice_id');
    }
}
