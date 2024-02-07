<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OL_CHL_TEST extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG_TEST.dbo.OrderLines';
    protected $guarded = []; 
    public $timestamps = false;
}
