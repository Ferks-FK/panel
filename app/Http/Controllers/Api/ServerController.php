<?php

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Application\Servers\DeleteServerRequest;
use App\Http\Requests\Api\Application\Servers\GetServersRequest;
use App\Http\Requests\Api\Application\Servers\SuspendingServerRequest;
use App\Http\Requests\Api\Application\Servers\UpdateServerRequest;
use App\Models\Server;
use App\Models\User;
use App\Settings\PterodactylSettings;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class ServerController extends Controller
{
    public const ALLOWED_INCLUDES = ['product', 'user'];
    public const ALLOWED_FILTERS = ['name', 'suspended', 'identifier', 'pterodactyl_id', 'user_id', 'product_id'];

    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  GetServersRequest  $request
     * @return LengthAwarePaginator
     */
    public function index(GetServersRequest $request)
    {
        $query = QueryBuilder::for(Server::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS);

        return $query->paginate($request->input('per_page') ?? 50);
    }

    /**
     * Display the specified resource.
     *
     * @param  GetServersRequest  $request
     * @param  string  $id
     * @return Server|Collection|Model
     */
    public function show(GetServersRequest $request, string $id)
    {
        $server = QueryBuilder::for(Server::class)
            ->where('id', $id)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->firstOrFail();

        return response()->json($server);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateServerRequest  $request
     * @param  string  $id
     * @return Server
     */
    public function update(UpdateServerRequest $request, string $id)
    {
        $data = $request->validated();

        $server = Server::findOrFail($id);

        $response = $this->pterodactyl->application->patch("application/servers/{$server->pterodactyl_id}/details", [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'user' => User::find($data['user_id'])->pterodactyl_id,
            'external_id' => $data['external_id'] ?? null,
        ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $response->toException()->getMessage(),
                'pterodactyl_error_status' => $response->toException()->getCode(),
            ]);
        }

        $server->update($data);

        return response()->json($server);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DeleteServerRequest  $request
     * @param  Server  $server
     * @return Server
     */
    public function destroy(DeleteServerRequest $request, Server $server)
    {
        $server->delete();

        return response()->json($server);
    }

    /**
     * suspend server
     *
     * @param  SuspendingServerRequest  $request
     * @param  Server  $server
     * @return Server|JsonResponse
     */
    public function suspend(SuspendingServerRequest $request, $server)
    {
        try {
            $server->suspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

        return $server->load('product');
    }

    /**
     * unsuspend server
     *
     * @param  SuspendingServerRequest  $request
     * @param  Server  $server
     * @return Server|JsonResponse
     */
    public function unSuspend(SuspendingServerRequest $request, Server $server)
    {
        try {
            $server->unSuspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

        return $server->load('product');
    }
}
