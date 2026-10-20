<?php

namespace App\Support;

use App\Models\DivePhoto;
use App\Models\DiverPhoto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * "Delete my account" (Pablo, 2026-10-19; App Store guideline 5.1.1(v):
 * an app that creates accounts must let people delete them).
 *
 * Deleted: everything personal - logbook (dives, photos and their files,
 * shared site-page copies, gear, own species, imports, own sites), visited
 * and wishlist sites, calendar entries, notifications, group memberships
 * and RSVPs, diver photos on site pages, app sign-ins, devices, sessions.
 *
 * Kept but anonymized: what other divers rely on - site reviews, ratings
 * and comments, group chat messages, conditions reports, groups the person
 * created. The user row stays as "Deleted diver" with every personal field
 * erased and an unusable email/password, so those rows still have an author
 * and nothing can sign in as it. The same email can register again later.
 */
class AccountDeleter
{
    public static function delete(User $user): void
    {
        abort_if($user->id === \App\Http\Middleware\AuthenticateAsGuest::GUEST_USER_ID, 403);
        $id = $user->id;

        // Files first (they aren't in the database transaction).
        foreach (DivePhoto::where('user_id', $id)->get() as $photo) {
            DivePhotoStore::delete($photo); // also removes a shared site-page copy
        }
        foreach (DiverPhoto::where('userId', $id)->get() as $photo) {
            $photo->deleteWithFile();
        }
        // Videos: the dive ones take their Cloudflare assets (and site copies) with them.
        foreach (\App\Models\DiveVideo::where('user_id', $id)->get() as $video) {
            DiveVideoStore::delete($video);
        }
        foreach (\App\Models\SiteVideo::where('userId', $id)->get() as $video) {
            $video->deleteWithStream();
        }
        foreach (\App\Models\GroupMessageVideo::where('user_id', $id)->get() as $video) {
            $video->deleteVideo();
        }
        File::deleteDirectory(storage_path('app/equipment/' . $id));
        File::deleteDirectory(storage_path('app/dive-photos/' . $id));
        if ($user->picture) {
            Storage::disk('siteAssets')->delete('img/users/' . $user->picture);
        }

        DB::transaction(function () use ($id) {
            $diveIds = DB::table('dives')->where('user_id', $id)->pluck('id');
            foreach ($diveIds->chunk(500) as $chunk) {
                DB::table('dive_species')->whereIn('dive_id', $chunk)->delete();
                DB::table('dive_equipment')->whereIn('dive_id', $chunk)->delete();
            }
            $ownSpecies = DB::table('species')->where('user_id', $id)->pluck('id');
            DB::table('dive_species')->whereIn('species_id', $ownSpecies)->delete();
            DB::table('dive_photo_species')->whereIn('species_id', $ownSpecies)->delete();
            DB::table('species')->where('user_id', $id)->delete();

            $configIds = DB::table('equipment_configs')->where('user_id', $id)->pluck('id');
            DB::table('equipment_config_items')->whereIn('equipment_config_id', $configIds)->delete();
            DB::table('equipment_configs')->where('user_id', $id)->delete();
            DB::table('equipment_items')->where('user_id', $id)->delete();

            foreach (['dives', 'dive_changes', 'dive_imports', 'user_sites', 'deco_plans', 'ccr_checklist_sessions',
                      'user_dive_gases', 'push_subscriptions', 'app_login_codes', 'conversation_messages', 'sessions'] as $t) {
                DB::table($t)->where('user_id', $id)->delete();
            }
            DB::table('personal_access_tokens')->where('tokenable_type', User::class)->where('tokenable_id', $id)->delete();
        });

        $trips = DB::connection('mysql_trips');
        $trips->transaction(function () use ($trips, $id) {
            foreach (['visitedSites' => 'userId', 'wishedSites' => 'userId', 'events' => 'userId', 'personaldives' => 'userId',
                      'messages' => 'userId', 'post_dive_feedback_requests' => 'user_id', 'group_members' => 'user_id',
                      'group_dive_rsvps' => 'user_id', 'group_message_reactions' => 'user_id'] as $t => $col) {
                $trips->table($t)->where($col, $id)->delete();
            }
            $trips->table('group_members')->where('invited_by', $id)->update(['invited_by' => null]);
        });

        // The account itself: nothing personal left, nothing can sign in.
        $user->forceFill([
            'name' => 'Deleted diver',
            'email' => 'deleted-' . $id . '-' . Str::random(8) . '@deleted.divers-hub.com',
            'password' => Hash::make(Str::random(64)),
            'remember_token' => null, 'email_verified_at' => null,
            'picture' => null, 'location' => null, 'google_id' => null, 'google_avatar_url' => null, 'apple_id' => null,
            'phone' => null, 'phone_verified_at' => null, 'pending_phone' => null,
            'phone_verification_code_hash' => null, 'phone_verification_sent_at' => null,
            'favOperators' => null, 'favLocations' => null, 'calendar_token' => null,
            'email_notifications' => 0, 'sms_notifications' => 0, 'whatsapp_notifications' => 0, 'newsletter_subscribed' => 0,
            'email_consent_at' => null, 'sms_consent_at' => null, 'whatsapp_consent_at' => null,
            'share_sightings' => null, 'last_seen_at' => null,
        ])->save();
    }
}
