<?php

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Application\Users\CreateUserRequest;
use App\Http\Requests\Api\Application\Users\DeleteUserRequest;
use App\Http\Requests\Api\Application\Users\GetUsersRequest;
use App\Http\Requests\Api\Application\Users\UpdateUserRequest;
use App\Models\User;
use App\Notifications\ReferralNotification;
use App\Settings\PterodactylSettings;
use App\Settings\ReferralSettings;
use App\Settings\UserSettings;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    const ALLOWED_INCLUDES = ['servers', 'notifications', 'payments', 'vouchers', 'roles', 'discordUser'];
    const ALLOWED_FILTERS = ['name', 'server_limit', 'email', 'pterodactyl_id', 'suspended'];

    private $pterodactyl;
    private $userSettings;
    private $referralSettings;

    public function __construct(PterodactylSettings $ptero_settings, UserSettings $userSettings, ReferralSettings $referralSettings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
        $this->userSettings = $userSettings;
        $this->referralSettings = $referralSettings;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  GetUsersRequest  $request
     * @return LengthAwarePaginator
     */
    public function index(GetUsersRequest $request)
    {
        $query = QueryBuilder::for(User::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS);

        return $query->paginate($request->input('per_page') ?? 50);
    }

    /**
     * Display the specified resource.
     *
     * @param  GetUsersRequest  $request
     * @param  int  $id
     * @return User|Builder|Collection|Model
     */
    public function show(GetUsersRequest $request, int $id)
    {
        $user = QueryBuilder::for(User::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUserRequest  $request
     * @param  int  $id
     * @return User
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $data = $request->validated();

        $user = User::findOrFail($id);

        //Update Users Password on Pterodactyl
        //Username,Mail,First and Lastname are required aswell
        $response = $this->pterodactyl->application->patch('/application/users/'. $user->pterodactyl_id, [
            'username' => $data['name'],
            'first_name' => $data['name'],
            'last_name' => $data['name'],
            'email' => $data['email'],
        ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $response->toException()->getMessage(),
                'pterodactyl_error_status' => $response->toException()->getCode(),
            ]);
        }

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);

            unset($data['role']);
        }

        if (isset($data['suspended'])) {
            if ($data['suspended'] && !$user->isSuspended()) {
                $user->suspend();
            } elseif (!$data['suspended'] && $user->isSuspended()) {
                $user->unSuspend();
            }

            unset($data['suspended']);
        }

        $user->update($data);

        if(isset($data['credits'])) {
            event(new UserUpdateCreditsEvent($user));
        }

        return response()->json($user);
    }

    /**
     * Create a unique Referral Code for User
     *
     * @return string
     */
    protected function createReferralCode()
    {
        $referralcode = STR::random(8);
        if (User::where('referral_code', '=', $referralcode)->exists()) {
            $this->createReferralCode();
        }

        return $referralcode;
    }

    /**
     * @throws ValidationException
     */
    public function store(CreateUserRequest $request)
    {
        $data = $request->validated();

        // Prevent the creation of new users via API if this is enabled.
        if (!$this->userSettings->creation_enabled) {
            throw ValidationException::withMessages([
                'error' => 'The creation of new users has been blocked by the system administrator.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'credits' => $data['credits'] ?? $this->userSettings->initial_credits,
            'server_limit' => $data['server_limit'] ?? $this->userSettings->initial_server_limit,
            'password' => $data['password'],
            'referral_code' => $this->createReferralCode(),
            'pterodactyl_id' => Str::uuid(),
        ]);

        $response = $this->pterodactyl->application->post('/application/users', [
            'external_id' => App::environment('local') ? Str::random(16) : (string) $user->id,
            'username' => $user->name,
            'email' => $user->email,
            'first_name' => $user->name,
            'last_name' => $user->name,
            'password' => $data['password'],
            'root_admin' => false,
            'language' => 'en',
        ]);

        if ($response->failed()) {
            $user->delete();
            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $response->toException()->getMessage(),
                'pterodactyl_error_status' => $response->toException()->getCode(),
            ]);
        }

        $user->update([
            'pterodactyl_id' => $response->json()['attributes']['id'],
        ]);

        $user->syncRoles([$data['role']]);

        //INCREMENT REFERRAL-USER CREDITS
        if (isset($data['referral_code'])) {
            $ref_user = User::query()->where('referral_code', '=', $data['referral_code'])->first();
            $new_user = $user->id;

            if ($ref_user) {
                if ($this->referralSettings->mode == 'register' || $this->referralSettings->mode == 'both') {
                    $ref_user->increment('credits', $this->referralSettings->reward);
                    $ref_user->notify(new ReferralNotification($ref_user->id, $new_user));
                }
                //INSERT INTO USER_REFERRALS TABLE
                DB::table('user_referrals')->insert([
                    'referral_id' => $ref_user->id,
                    'registered_user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $user->sendEmailVerificationNotification();

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DeleteUserRequest  $request
     * @param  int  $id
     * @return Application|Response|ResponseFactory
     */
    public function destroy(DeleteUserRequest $request, int $id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return response()->json($user);
    }
}
