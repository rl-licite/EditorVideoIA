<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Services\CreditService;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('active', true)->get();

        if ($plans->count() === 0) {
            $plans = collect([
                SubscriptionPlan::create(['name' => 'Iniciante', 'monthly_credits' => 100, 'price' => 49.90, 'max_videos' => 100]),
                SubscriptionPlan::create(['name' => 'Profissional', 'monthly_credits' => 500, 'price' => 99.90, 'max_videos' => 500]),
                SubscriptionPlan::create(['name' => 'Avançado', 'monthly_credits' => 1500, 'price' => 199.90, 'max_videos' => 1500]),
            ]);
        }

        return view('plans.index', compact('plans'));
    }

    public function subscribe(SubscriptionPlan $plan, CreditService $creditService)
    {
        $user = Auth::user();
        $user->update(['subscription_plan_id' => $plan->id]);
        $creditService->add($user, $plan->monthly_credits, 'Créditos adicionados pelo plano ' . $plan->name . '.');

        return redirect()->route('plans.index')->with('success', 'Plano ativado em modo teste. Créditos adicionados.');
    }
}
