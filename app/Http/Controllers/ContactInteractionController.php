<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactInteractionRequest;
use App\Models\ContactInteraction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactInteractionController extends Controller
{
    public function store(StoreContactInteractionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;

        $interaction = ContactInteraction::query()->create($data);

        return redirect("/contacts/{$interaction->contact_id}")
            ->with('status', 'Gorusme notu kaydedildi.');
    }

    public function toggleFollowUp(Request $request, ContactInteraction $contactInteraction): RedirectResponse
    {
        $this->authorize('update', $contactInteraction);

        if ($contactInteraction->follow_up_due_at === null) {
            return redirect("/contacts/{$contactInteraction->contact_id}")
                ->with('status', 'Takip tarihi olmayan kayit tamamlanamaz.');
        }

        $wasCompleted = $contactInteraction->follow_up_completed_at !== null;
        $contactInteraction->update([
            'follow_up_completed_at' => $wasCompleted ? null : now(),
        ]);

        $target = $request->headers->get('referer', "/contacts/{$contactInteraction->contact_id}");

        return redirect($target)->with(
            'status',
            $wasCompleted ? 'Gorusme takibi tekrar acildi.' : 'Gorusme takibi tamamlandi.',
        );
    }
}
