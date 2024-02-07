<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OH extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG.dbo.IT_OrderHeader';
    protected $guarded = []; 
    public $timestamps = false;
     protected $primaryKey = "ID";
}
