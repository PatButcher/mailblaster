<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_mailing_list');
    }
}
