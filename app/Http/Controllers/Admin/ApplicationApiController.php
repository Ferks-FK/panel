<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationApi;
use App\Settings\LocaleSettings;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApplicationApiController extends Controller
{
    const READ_PERMISSION = "admin.api.read";
    const WRITE_PERMISSION = "admin.api.write";

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(LocaleSettings $locale_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::WRITE_PERMISSION]);

        return view('admin.api.index', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $permissions = config('permissions_api');

        return view('admin.api.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $data = $request->validate([
            'description' => 'required|string|max:255',
            'allowed_ips' => 'nullable|array',
            'abilities' => 'required|array',
            'abilities.*' => 'array'
        ]);

        $abilities = [];

        foreach ($data['abilities'] as $resource => $permissions) {
            foreach ($permissions as $permission) {
                $abilities[] = "{$resource}:{$permission}";
            }
        }

        $request->user()->createToken($data['description'], $abilities, $data['allowed_ips'] ?? []);

        return redirect()->route('admin.api.index')
            ->with('success', __('API token created successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ApplicationApi  $applicationApi
     * @return RedirectResponse
     */
    public function destroy(ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $applicationApi->delete();

        return redirect()->back()->with('success', __('api key has been removed!'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse|mixed
     *
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $query = ApplicationApi::query();

        return datatables($query)
            ->editColumn('id', function (ApplicationApi $apiKey) {
                return $apiKey->id;
            })
            ->addColumn('actions', function (ApplicationApi $apiKey) {
                return '
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.api.destroy', $apiKey->id).'">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                           <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->editColumn('token', function (ApplicationApi $apiKey) {
                $token = decrypt($apiKey->token);

                return "<code>{$token}</code>";
            })
            ->editColumn('last_used_at', function (ApplicationApi $apiKey) {
                return $apiKey->last_used_at ? $apiKey->last_used_at->diffForHumans() : '';
            })
            ->rawColumns(['actions', 'token'])
            ->make();
    }
}
