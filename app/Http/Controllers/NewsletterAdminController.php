<?php

namespace App\Http\Controllers;

use App\Models\NewsletterIssue;
use App\Services\NewsletterService;
use App\Support\NewsletterMarkdown;
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
        $document = view('emails.newsletter-document', array_merge($this->content($issue), [
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
        $ok = NewsletterService::sendToUser(auth()->user(), '[TEST] ' . $issue->subject, $this->content($issue));

        return back()->with($ok ? 'success' : 'error', $ok ? 'Test sent to ' . auth()->user()->email . '.' : 'Test send failed - check the logs.');
    }

    /**
     * The real, platform-wide send. Synchronous - there is no queue
     * worker in this app (every other bulk send runs from a CLI/cron
     * path, not a web request - see SendGroupDiveReminders,
     * DetectCancelledTrips). Fine at today's subscriber count; if that
     * grows enough to risk a request timeout, this needs to move to a
     * queued job instead of the raised time limit below.
     */
    public function send(NewsletterIssue $issue)
    {
        abort_if($issue->isSent(), 403, 'This issue was already sent.');

        set_time_limit(300);

        $recipients = NewsletterService::subscribedRecipients();
        $content = $this->content($issue);
        $sent = $recipients->filter(fn ($user) => NewsletterService::sendToUser($user, $issue->subject, $content))->count();

        $issue->update(['status' => 'sent', 'sent_at' => now(), 'sent_count' => $sent]);

        return redirect()->route('Newsletter.manage.index')->with('success', "Sent to {$sent} of {$recipients->count()} subscribers.");
    }

    private function content(NewsletterIssue $issue): array
    {
        return [
            'preheader' => $issue->preheader ?: $issue->headline,
            'date' => now()->format('F j, Y'),
            'headline' => $issue->headline,
            'body' => NewsletterMarkdown::toHtml($issue->body_markdown),
            'conditions' => $issue->conditions ?: '',
        ];
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
