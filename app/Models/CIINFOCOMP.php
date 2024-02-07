<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CIINFOCOMP extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG.dbo.CIINFOCOMP';
    protected $guarded = []; 
    public $timestamps = false;
}
