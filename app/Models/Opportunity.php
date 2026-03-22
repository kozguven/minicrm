<?php

namespace App\Models;

use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['contact_id', 'opportunity_stage_id', 'title', 'value', 'expected_close_date'])]
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
     * @return BelongsTo<OpportunityStage, $this>
     */
    public function opportunityStage(): BelongsTo
    {
        return $this->belongsTo(OpportunityStage::class, 'opportunity_stage_id');
    }

    /**
     * Backwards-friendly alias for the current opportunity stage.
     *
     * @return BelongsTo<OpportunityStage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->opportunityStage();
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
}
