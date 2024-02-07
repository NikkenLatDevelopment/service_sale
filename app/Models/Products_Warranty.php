<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products_Warranty extends Model
{
    use HasFactory;
    protected $connection = 'mysqlTV';
    protected $table = 'products_warranty';
    protected $guarded = []; 
}
