<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApiTokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiTokenRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $ability = $request->string('ability')->toString();

        return view('cms.api-tokens.index', [
            'users' => User::query()
                ->select(['id', 'name', 'email'])
                ->orderBy('name', 'asc')
                ->get(),
            'tokens' => PersonalAccessToken::query()
                ->where('tokenable_type', User::class)
                ->with('tokenable')
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $subQuery) use ($search): void {
                        $subQuery->where('name', 'like', "%{$search}%")
                            ->orWhereHas('tokenable', function (Builder $userQuery) use ($search): void {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($ability !== '', fn (Builder $query): Builder => $query->whereJsonContains('abilities', $ability))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'ability_options' => $this->abilityOptions(),
            'filters' => [
                'search' => $search,
                'ability' => $ability,
            ],
        ]);
    }

    public function store(StoreApiTokenRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->findOrFail($validated['user_id']);
        $abilities = array_values(array_unique($validated['abilities']));
        $token = $user->createToken($validated['name'], $abilities);

        return redirect()
            ->route('cms.api-tokens.index')
            ->with('status', 'API token created successfully.')
            ->with('generated_token', $token->plainTextToken)
            ->with('generated_token_name', $validated['name'])
            ->with('generated_token_user_name', $user->name)
            ->with('generated_token_user_email', $user->email)
            ->with('generated_token_abilities', $abilities);
    }

    public function destroy(PersonalAccessToken $token): RedirectResponse
    {
        $token->delete();

        return redirect()
            ->route('cms.api-tokens.index')
            ->with('status', 'API token revoked successfully.');
    }

    private function abilityOptions(): array
    {
        return ApiTokenAbility::options();
    }
}
