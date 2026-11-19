<?php

namespace App\Http\Middleware;

use App\Support\ShopAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Route gate for the shop portal: ->middleware('operator.admin').
 *
 * 404, not 403, for anyone without a grant: the portal should not confirm
 * that it exists to someone who cannot use it. Routes that carry an
 * {operator} id are checked against that operator; the rest only need the
 * person to manage at least one.
 */
class OperatorAdminGate
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless(ShopAccess::hasPortal($user), 404);

        $operatorId = $request->route('operator');
        if ($operatorId !== null) {
            abort_unless(ctype_digit((string) $operatorId) && ShopAccess::canManage($user, (int) $operatorId), 404);
        }

        return $next($request);
    }
}
