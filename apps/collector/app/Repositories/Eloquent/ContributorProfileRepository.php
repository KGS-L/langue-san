<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContributorProfileRepositoryInterface;
use App\Models\CollectionSession;
use App\Models\Contribution;
use App\Models\ContributorConsent;
use App\Models\ContributorProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContributorProfileRepository implements ContributorProfileRepositoryInterface
{
    public function __construct(private readonly ContributorProfile $model) {}

    public function findByUserId(int $userId): ?ContributorProfile
    {
        return $this->model->newQuery()->where('user_id', $userId)->first();
    }

    public function findByGuestTokenHash(string $tokenHash): ?ContributorProfile
    {
        return $this->model->newQuery()->where('guest_token_hash', $tokenHash)->first();
    }

    public function create(array $data): ContributorProfile
    {
        return $this->model->newQuery()->create($data);
    }

    public function touchLastSeen(ContributorProfile $profile): void
    {
        $profile->forceFill(['last_seen_at' => now()])->save();
    }

    public function claimGuestForUser(string $tokenHash, User $user): ?ContributorProfile
    {
        return DB::transaction(function () use ($tokenHash, $user) {
            $guest = $this->findByGuestTokenHash($tokenHash);
            $accountProfile = $this->findByUserId($user->id);

            if (! $guest) {
                return $accountProfile;
            }

            if (! $accountProfile) {
                $guest->update(['user_id' => $user->id]);
                return $guest->refresh();
            }

            if ($accountProfile->is($guest)) {
                return $accountProfile;
            }

            CollectionSession::query()
                ->where('contributor_profile_id', $guest->id)
                ->update(['contributor_profile_id' => $accountProfile->id]);

            Contribution::query()
                ->where('contributor_profile_id', $guest->id)
                ->update(['contributor_profile_id' => $accountProfile->id]);

            foreach ($guest->consents()->get() as $consent) {
                ContributorConsent::query()->updateOrCreate(
                    [
                        'contributor_profile_id' => $accountProfile->id,
                        'consent_version_id' => $consent->consent_version_id,
                    ],
                    [
                        'accepted_at' => $consent->accepted_at,
                        'ip_hash' => $consent->ip_hash,
                    ],
                );
            }

            $accountProfile->fill([
                'locality_id' => $accountProfile->locality_id ?? $guest->locality_id,
                'locality_other' => $accountProfile->locality_other ?? $guest->locality_other,
                'fluency_level' => $accountProfile->fluency_level ?? $guest->fluency_level,
                'can_write_san' => $accountProfile->can_write_san ?? $guest->can_write_san,
                'guest_token_hash' => $accountProfile->guest_token_hash ?? $guest->guest_token_hash,
                'last_seen_at' => now(),
            ])->save();

            $guest->delete();

            return $accountProfile->refresh();
        });
    }
}
