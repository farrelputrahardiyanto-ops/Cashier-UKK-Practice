<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice_Detail extends Model
{
    protected $table = 'invoice_detail';

    protected $fillable = [
        'invoice_id',
        'product_id',
        'qty',
        'subtotal'
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
