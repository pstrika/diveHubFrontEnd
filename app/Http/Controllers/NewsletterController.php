<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * One-click newsletter unsubscribe/resubscribe (Pablo, 2026-09-21: "let's
 * manage it ourselves" rather than Mailgun's domain-wide suppression list -
 * that list is shared across every Mailgun template this app sends, so
 * unsubscribing from the newsletter would have silently suppressed trip
 * reminders and cancellation notices too). newsletter_subscribed lives on
 * User directly and is the only source of truth - see
 * Console\Commands\SendNewsletter for the sender side.
 *
 * Reached from a signed URL (Illuminate\Routing\Middleware\ValidateSignature
 * via the 'signed' route middleware) built with URL::signedRoute() and no
 * expiration - CAN-SPAM requires an unsubscribe mechanism to keep working
 * for at least 30 days after send, so this is deliberately not
 * temporarySignedRoute().
 */
class NewsletterController extends Controller
{
    public function unsubscribe(User $user)
    {
        $user->newsletter_subscribed = false;
        $user->save();

        return view('pages.Newsletter.Unsubscribed', [
            'user' => $user,
            'resubscribeUrl' => URL::signedRoute('Newsletter.resubscribe', ['user' => $user->id]),
        ]);
    }

    public function resubscribe(User $user)
    {
        $user->newsletter_subscribed = true;
        $user->save();

        return view('pages.Newsletter.Resubscribed', ['user' => $user]);
    }
}
