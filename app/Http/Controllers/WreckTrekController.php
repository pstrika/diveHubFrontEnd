<?php

namespace App\Http\Controllers;

use App\Models\WreckTrek;
use App\Models\WreckTrekRoute;
use App\Support\WreckTrekData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Wreck Trek (spec 2026-11-04): plan a multi-wreck navigation dive, for
 * registered members only. The page is plain JavaScript over GET api/wrecktrek; routes are the only thing it
 * writes. Legs and wrecks are edited by admins in the site editor
 * (WreckTrekAdminController). These sit in web.php, not api.php, because the
 * page signs in with the browser session rather than a Sanctum token.
 */
class WreckTrekController extends Controller
{
    public function index()
    {
        abort_unless($this->memberId(), 403);

        $SEO = [
            'title' => 'Wreck Trek Planner: Pompano and Key Biscayne | Divers Hub',
            'desc' => 'Plan a multi-wreck navigation dive on the Pompano or Key Biscayne wreck trek: compass headings to steer, swim time per leg and a depth profile.',
            'keywords' => 'wreck trek, pompano beach wreck trek, key biscayne wreck trek, wreck navigation planner, dive route planner, current correction scuba',
            'canonical' => route('WreckTrek'),
        ];

        $treks = WreckTrek::orderBy('sort')->pluck('name', 'slug')->all();

        return view('pages.WreckTrek', compact('SEO', 'treks'));
    }

    /** Whole module; adds the caller's own private routes when signed in. */
    public function data()
    {
        $id = $this->memberId();
        abort_unless($id, 403);

        $data = WreckTrekData::load();
        $own = WreckTrekRoute::where('user_id', $id)->orderBy('name')->get()->map(fn ($r) => $r->toApi($id))->all();
        $ownIds = array_column($own, 'id');
        $data['routes'] = array_merge(
            array_values(array_filter($data['routes'], fn ($r) => !in_array($r['id'], $ownIds, true))),
            $own
        );

        return response()->json($data);
    }

    public function show(int $id)
    {
        $viewer = $this->memberId();
        abort_unless($viewer, 403);

        $route = WreckTrekRoute::findOrFail($id);
        abort_unless($route->is_public || ($viewer && (int) $route->user_id === $viewer), 404);

        return response()->json($route->toApi($viewer));
    }

    public function store(Request $request)
    {
        $id = $this->memberId();
        abort_unless($id, 401);

        if ($error = $this->problem($request, true)) return $error;

        $route = WreckTrekRoute::create([
            'user_id' => $id,
            'name' => $request->input('name'),
            'stops' => array_values($request->input('stops')),
            'is_public' => false,
        ]);

        return response()->json($route->toApi($id), 201);
    }

    public function update(Request $request, int $id)
    {
        $route = $this->owned($id);

        if ($error = $this->problem($request, false)) return $error;

        if ($request->has('name')) $route->name = $request->input('name');
        if ($request->has('stops')) $route->stops = array_values($request->input('stops'));
        $route->save();

        return response()->json($route->toApi($route->user_id));
    }

    public function destroy(int $id)
    {
        $this->owned($id)->delete();

        return response()->noContent();
    }

    /** A 400 naming the first failure, so the page can show it, or null. */
    private function problem(Request $request, bool $required)
    {
        $need = $required ? 'required' : 'sometimes';
        $validator = Validator::make($request->all(), [
            'name' => $need . '|string|min:1|max:60',
            'stops' => $need . '|array|min:2|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'pair' => null], 400);
        }

        if ($request->has('stops') && ($problem = WreckTrekData::stopsProblem(array_values($request->input('stops'))))) {
            return response()->json($problem, 400);
        }

        return null;
    }

    private function owned(int $id): WreckTrekRoute
    {
        $viewer = $this->memberId();
        abort_unless($viewer, 401);
        $route = WreckTrekRoute::findOrFail($id);
        abort_unless((int) $route->user_id === $viewer, 403);

        return $route;
    }

    /** The automatic guest account every visitor gets is not "signed in". */
    private function memberId(): ?int
    {
        $user = auth()->user();

        return $user && $user->isNotGuest() ? (int) $user->id : null;
    }
}
