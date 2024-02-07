<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CIINFO_TEST extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG_TEST.dbo.CIINFO';
    protected $guarded = []; 
    public $timestamps = false;
}
