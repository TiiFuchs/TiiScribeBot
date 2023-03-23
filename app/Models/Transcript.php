<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{

    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'text' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
