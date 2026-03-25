<?php

namespace App\Models;

use Database\Factories\ContactInteractionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'contact_id',
    'user_id',
    'channel',
    'happened_at',
    'summary',
    'notes',
    'follow_up_due_at',
    'follow_up_completed_at',
])]
class ContactInteraction extends Model
{
    /** @use HasFactory<ContactInteractionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
            'follow_up_due_at' => 'datetime',
            'follow_up_completed_at' => 'datetime',
        ];
    }
}
