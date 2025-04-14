<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    protected $table = 'faq';

    protected $fillable = [
        'question',
        'answer',
        'order'
    ];
}
