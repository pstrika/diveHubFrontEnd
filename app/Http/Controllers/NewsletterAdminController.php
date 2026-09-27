<?php

namespace App\Http\Controllers;

use App\Models\NewsletterIssue;
use App\Services\NewsletterService;
use App\Support\WeekendConditions;
use Illuminate\Http\Request;

/**
 * On-demand newsletter composer (Pablo, 2026-09-21: "we also probably
 * need an interface to create newsletters content on demand. We will use
 * this same template for the weekly digest, but ad hoc newsletters are
 * also useful to communicate platform wide"). Admin-only ('admin' route
 * middleware - AdminMiddleware, role_id == 1) since this reaches every
 * subscribed user at once, unlike the Blog's Creator/Admin split.
 *
 * A sent issue is kept as a record but is no longer editable or
 * re-sendable in full - see edit()/update()/send() guards. "Send test"
 * has no such restriction; send it as many times as useful while drafting.
 */
class NewsletterAdminController extends Controller
{
    public function index()
    {
        $issues = NewsletterIssue::with('author')->latest()->get();

        return view('pages.Newsletter.Manage.Index', compact('issues'));
    }

    public function create()
    {
        return view('pages.Newsletter.Manage.Form', ['issue' => new NewsletterIssue()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $issue = NewsletterIssue::create($data);

        return redirect()->route('Newsletter.manage.edit', $issue)->with('success', 'Draft saved.');
    }

    public function edit(NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'A sent issue can no longer be edited.');

        return view('pages.Newsletter.Manage.Form', compact('issue'));
    }

    public function update(Request $request, NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'A sent issue can no longer be edited.');

        $issue->update($this->validated($request));

        return redirect()->route('Newsletter.manage.edit', $issue)->with('success', 'Draft saved.');
    }

    public function destroy(NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'A sent issue can no longer be deleted.');

        $issue->delete();

        return redirect()->route('Newsletter.manage.index')->with('success', 'Draft removed.');
    }

    /** Renders the actual email HTML from the saved issue, opened in a new tab - same idea as BlogAdminController::preview(). */
    public function preview(NewsletterIssue $issue)
    {
        $document = view('emails.newsletter-document', array_merge(NewsletterService::contentFor($issue), [
            // A real signed link would work fine here too, but a preview
            // isn't addressed to anyone in particular - a dead-looking
            // link makes that obvious rather than implying this preview
            // IS the email a real subscriber got.
            'unsubscribeUrl' => '#preview-not-a-real-recipient',
        ]))->render();

        return response($document);
    }

    /** Sends only to the current admin - fast, safe, repeatable while drafting. */
    public function sendTest(NewsletterIssue $issue)
    {
        $result = NewsletterService::sendToUser(auth()->user(), '[TEST] ' . $issue->subject, NewsletterService::contentFor($issue));

        return $result === true
            ? back()->with('success', 'Test sent to ' . auth()->user()->email . '.')
            : back()->with('error', 'Test send failed: ' . $result);
    }

    /** The real, platform-wide send, right now - see NewsletterService::sendIssueToAllSubscribers() for why this is synchronous. */
    public function send(NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'This issue was already sent.');

        $recipientCount = NewsletterService::subscribedRecipients()->count();
        $sent = NewsletterService::sendIssueToAllSubscribers($issue);

        return redirect()->route('Newsletter.manage.index')->with('success', "Sent to {$sent} of {$recipientCount} subscribers.");
    }

    /** Sets a future send time - the scheduled-send cron path (SendScheduledNewsletters) picks it up once it arrives. */
    public function schedule(Request $request, NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'This issue was already sent.');

        $data = $request->validate(['scheduled_at' => 'required|date|after:now']);
        $issue->update(['scheduled_at' => $data['scheduled_at']]);

        return back()->with('success', 'Scheduled for ' . $issue->fresh()->scheduled_at->format('M j, Y \a\t g:i A') . '.');
    }

    public function unschedule(NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'This issue was already sent.');

        $issue->update(['scheduled_at' => null]);

        return back()->with('success', 'Scheduled send cancelled.');
    }

    /** AJAX: a starting-point sentence for the conditions field, from real forecast data - see App\Support\WeekendConditions. */
    public function generateConditions()
    {
        return response()->json(['summary' => WeekendConditions::summary()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'subject' => 'required|string|max:255',
            'preheader' => 'nullable|string|max:255',
            'headline' => 'required|string|max:255',
            'body_markdown' => 'required|string',
            'conditions' => 'nullable|string|max:255',
        ]);
    }
}
