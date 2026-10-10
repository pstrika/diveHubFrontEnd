<?php

namespace App\Support;

use App\Models\GroupMessage;

/**
 * Turns a group chat message's plain-text body into safe HTML - escapes
 * the whole body first, then replaces each of the message's own #site
 * mentions (GroupMessage::siteMentions, set at send time by
 * GroupMessageController@store from the composer's own picked sites, see
 * ChatCard.blade.php) with a link to that site's SiteDetails page.
 *
 * This is the only place a chat message's body is allowed to carry raw
 * HTML - every fragment in the replacement is either e()-escaped text or
 * a server-built URL, never unescaped user input. Unlike @mention
 * (MentionParser), this never re-scans the body against the whole sites
 * table - only against the handful of sites this one message actually
 * mentions, which is both cheap and exact (no risk of matching a site
 * name the diver never actually picked).
 */
final class ChatMessageRenderer
{
    public static function html(GroupMessage $message): string
    {
        $body = e((string) $message->body);

        // Longest name first, same reasoning MentionParser documents for
        // @mention: if one mentioned site's name is a substring of
        // another's ("Blue Heron Bridge" vs "Blue Heron Bridge North"),
        // the longer one has to be replaced first or the shorter name's
        // replacement would corrupt it.
        $mentions = $message->siteMentions
            ->filter(fn ($m) => $m->site)
            ->unique(fn ($m) => $m->site_id)
            ->sortByDesc(fn ($m) => mb_strlen($m->site->name));

        foreach ($mentions as $mention) {
            $site = $mention->site;
            $needle = '#' . e($site->name);
            $url = route('SiteDetails') . '/' . ($site->slug ?? $site->id);
            $link = '<a href="' . e($url) . '" class="dh-chat-mention">#' . e($site->name) . '</a>';
            $body = str_replace($needle, $link, $body);
        }

        return $body;
    }
}
