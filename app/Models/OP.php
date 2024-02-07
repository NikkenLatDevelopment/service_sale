<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OP extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG.dbo.IT_OrderPayments';
    protected $guarded = []; 
    public $timestamps = false;
     protected $primaryKey = "ID";
}
