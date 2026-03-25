<?php

namespace App\Services\Automation;

use App\Models\OpportunityStage;
use Illuminate\Support\Str;

class StageTaskTemplateService
{
    /**
     * @return array{title: string, priority: string, due_in_hours: int}
     */
    public function templateForStage(OpportunityStage $stage): array
    {
        $stageName = trim($stage->name);
        $normalized = Str::lower($stageName);

        if (Str::contains($normalized, 'teklif')) {
            return [
                'title' => 'Teklif asamasi teklif dosyasini guncelle',
                'priority' => 'high',
                'due_in_hours' => 6,
            ];
        }

        if (Str::contains($normalized, 'muzakere') || Str::contains($normalized, 'müzakere')) {
            return [
                'title' => 'Muzakere asamasi itiraz yonetimini tamamla',
                'priority' => 'high',
                'due_in_hours' => 8,
            ];
        }

        if (Str::contains($normalized, 'kapan') || Str::contains($normalized, 'kazan')) {
            return [
                'title' => 'Kapanis asamasi son onayi al',
                'priority' => 'high',
                'due_in_hours' => 4,
            ];
        }

        return [
            'title' => "{$stageName} asamasi sonrasi takip gorevi",
            'priority' => 'medium',
            'due_in_hours' => 24,
        ];
    }
}
