<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqTurn extends Model
{
    protected $fillable = [
        'locale',
        'question',
        'answer',
        'matched_slug',
    ];
}
