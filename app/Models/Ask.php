<?php

namespace App\Models;

use Database\Factories\AskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'source',
    'session_id',
    'status',
    'question',
    'reply',
])]
class Ask extends Model
{
    /** @use HasFactory<AskFactory> */
    use HasFactory;
}
