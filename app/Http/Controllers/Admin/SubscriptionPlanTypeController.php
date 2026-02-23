<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlanType;
use Illuminate\Http\Request;

class SubscriptionPlanTypeController extends Controller
{
    private function ensureFullAccess(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Full admin access required to manage Subscription Plan Types.');
        }
    }

    public function index()
    {
        $this->ensureFullAccess();

        $planTypes = SubscriptionPlanType::orderBy('subscription_type')->orderBy('name')->paginate(15);

        return view('admin.subscription-plan-types.index', compact('planTypes'));
    }

    public function create()
    {
        $this->ensureFullAccess();

        return view('admin.subscription-plan-types.create');
    }

    public function store(Request $request)
    {
        $this->ensureFullAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'subscription_type' => 'required|string|in:starlink,omada',
            'billing_type' => 'required|string|in:monthly,yearly,custom',
            'billing_interval_months' => 'nullable|integer|min:1|max:24|required_if:billing_type,custom',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        if (($validated['billing_type'] ?? '') !== 'custom') {
            $validated['billing_interval_months'] = null;
        }

        SubscriptionPlanType::create($validated);

        return redirect()->route('admin.subscription-plan-types.index')
            ->with('success', 'Subscription plan type created successfully.');
    }

    public function edit(SubscriptionPlanType $subscriptionPlanType)
    {
        $this->ensureFullAccess();

        return view('admin.subscription-plan-types.edit', compact('subscriptionPlanType'));
    }

    public function update(Request $request, SubscriptionPlanType $subscriptionPlanType)
    {
        $this->ensureFullAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'subscription_type' => 'required|string|in:starlink,omada',
            'billing_type' => 'required|string|in:monthly,yearly,custom',
            'billing_interval_months' => 'nullable|integer|min:1|max:24|required_if:billing_type,custom',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        if (($validated['billing_type'] ?? '') !== 'custom') {
            $validated['billing_interval_months'] = null;
        }

        $subscriptionPlanType->update($validated);

        return redirect()->route('admin.subscription-plan-types.index')
            ->with('success', 'Subscription plan type updated successfully.');
    }

    public function destroy(SubscriptionPlanType $subscriptionPlanType)
    {
        $this->ensureFullAccess();

        $subscriptionPlanType->delete();

        return redirect()->route('admin.subscription-plan-types.index')
            ->with('success', 'Subscription plan type deleted successfully.');
    }
}
