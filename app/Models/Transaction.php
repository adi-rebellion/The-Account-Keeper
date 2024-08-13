<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [

        'trans_user_id',
        'trans_date',
        'trans_amount',
        'trans_type',
        'category_id',
        'description'
    ];
}
