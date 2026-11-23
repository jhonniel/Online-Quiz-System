<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OtherSubscriptionRequest;
use App\Models\OtherSubscription;
use App\Models\OtherSubscriptionProvider;
use Illuminate\Http\Request;

class OtherSubscriptionController extends Controller
{
    private function ensureCanAccess(): void
    {
        if (auth()->user()?->canAccessSubscriptionFeature('others')) {
            return;
        }

        abort(403, 'You do not have permission to manage Other Subscriptions.');
    }

    public function index(Request $request)
    {
        $this->ensureCanAccess();

        $query = OtherSubscription::query()
            ->with('subscriptionProvider')
            ->orderByRaw('CASE WHEN ends_on >= ? THEN 0 ELSE 1 END', [now()->toDateString()])
            ->orderBy('ends_on');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term, $search) {
                $q->where('name', 'like', $term)
                    ->orWhere('client_name', 'like', $term)
                    ->orWhere('client_email', 'like', $term)
                    ->orWhere('provider', 'like', $term)
                    ->orWhere('billing_type', 'like', $term)
                    ->orWhere('currency', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('subscriptionProvider', function ($providerQuery) use ($term) {
                        $providerQuery->where('name', 'like', $term);
                    });

                $lower = strtolower($search);
                if ($lower === 'active') {
                    $q->orWhereDate('ends_on', '>=', now()->toDateString());
                } elseif ($lower === 'expired') {
                    $q->orWhereDate('ends_on', '<', now()->toDateString());
                } elseif (in_array($lower, ['monthly', 'annual'], true)) {
                    $q->orWhere('billing_type', $lower);
                }
            });
        }

        $subscriptions = $query->paginate(15)->withQueryString();
        $activeCount = OtherSubscription::active()->count();
        $expiredCount = OtherSubscription::expired()->count();
        $upcomingCount = OtherSubscription::active()
            ->whereDate('ends_on', '<=', now()->addDays(30)->toDateString())
            ->count();

        return view('admin.other-subscriptions.index', compact(
            'subscriptions',
            'search',
            'activeCount',
            'expiredCount',
            'upcomingCount'
        ));
    }

    public function create()
    {
        $this->ensureCanAccess();

        return view('admin.other-subscriptions.create', [
            'subscription' => null,
            'providers' => OtherSubscriptionProvider::query()->orderBy('name')->get(),
        ]);
    }

    public function store(OtherSubscriptionRequest $request)
    {
        $this->ensureCanAccess();

        $payload = $this->payloadFromRequest($request);
        OtherSubscription::create($payload);

        return redirect()->route('admin.other-subscriptions.index')
            ->with('success', 'Subscription added successfully.');
    }

    public function edit(OtherSubscription $otherSubscription)
    {
        $this->ensureCanAccess();

        return view('admin.other-subscriptions.edit', [
            'subscription' => $otherSubscription,
            'providers' => OtherSubscriptionProvider::query()->orderBy('name')->get(),
        ]);
    }

    public function update(OtherSubscriptionRequest $request, OtherSubscription $otherSubscription)
    {
        $this->ensureCanAccess();

        $validated = $request->validated();
        $payload = $this->payloadFromRequest($request);
        $endsChanged = isset($payload['ends_on'])
            && $otherSubscription->ends_on?->toDateString() !== (string) $payload['ends_on'];

        $otherSubscription->fill($payload);

        if ($endsChanged) {
            $otherSubscription->last_notified_at = null;
            $otherSubscription->last_notified_for_ends_on = null;
        }

        $otherSubscription->save();

        return redirect()->route('admin.other-subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }

    public function destroy(OtherSubscription $otherSubscription)
    {
        $this->ensureCanAccess();

        $otherSubscription->delete();

        return redirect()->route('admin.other-subscriptions.index')
            ->with('success', 'Subscription removed successfully.');
    }


    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(OtherSubscriptionRequest $request): array
    {
        $validated = $request->validated();
        $newProviderName = trim((string) ($validated['new_provider_name'] ?? ''));
        $providerId = $validated['other_subscription_provider_id'] ?? null;

        if ($newProviderName !== '') {
            $provider = OtherSubscriptionProvider::query()->firstOrCreate(
                ['name' => $newProviderName],
                ['name' => $newProviderName]
            );
            $providerId = $provider->id;
        }

        $provider = $providerId
            ? OtherSubscriptionProvider::query()->find($providerId)
            : null;

        unset($validated['new_provider_name']);

        $validated['other_subscription_provider_id'] = $provider?->id;
        $validated['provider'] = $provider?->name;

        return $validated;
    }
}
