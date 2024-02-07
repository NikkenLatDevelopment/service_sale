<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPartnerAccInfo extends Model
{
    use HasFactory;
    protected $connection = '170';
    protected $table = 'NIKKENREG_STG.dbo.BusinessPartnerAccInfo';
    protected $guarded = []; 
    public $timestamps = false;
}
