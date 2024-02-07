<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sales_payments extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'sales_information_payments';
    protected $guarded = []; 
}
