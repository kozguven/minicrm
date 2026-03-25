<?php

namespace App\Models;

use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'contact_id',
    'owner_user_id',
    'opportunity_stage_id',
    'title',
    'value',
    'probability',
    'expected_close_date',
    'next_step',
    'next_step_due_at',
    'health_status',
])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
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
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * @return BelongsTo<OpportunityStage, $this>
     */
    public function opportunityStage(): BelongsTo
    {
        return $this->belongsTo(OpportunityStage::class, 'opportunity_stage_id');
    }

    /**
     * @return HasMany<CrmTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(CrmTask::class);
    }

    /**
     * @return HasOne<Deal, $this>
     */
    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'next_step_due_at' => 'datetime',
        ];
    }
}
