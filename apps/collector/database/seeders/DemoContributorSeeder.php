<?php

namespace Database\Seeders;

use App\Enums\AgeRange;
use App\Enums\ProfessionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ContributorProfile;
use App\Models\Locality;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoContributorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('DemoContributorSeeder ignoré en production.');
            return;
        }

        Role::findOrCreate(UserRole::CONTRIBUTOR->value, 'web');

        $firstNames = [
            'Adama', 'Aïssata', 'Aminata', 'Awa', 'Bintou',
            'Boureima', 'Daouda', 'Fatimata', 'Habibou', 'Hamidou',
            'Idrissa', 'Issa', 'Kadidia', 'Karim', 'Mariam',
            'Moussa', 'Oumar', 'Salimata', 'Seydou', 'Souleymane',
        ];

        // Noms utilisés ici uniquement comme données de démonstration plausibles.
        // Ils ne correspondent pas à de vraies personnes enregistrées dans le projet.
        $surnames = ['SO', 'SOW', 'KI', 'KY', 'TOE'];

        $professions = [
            ProfessionType::STUDENT,
            ProfessionType::TEACHER,
            ProfessionType::COMMUNITY_FIELD,
            ProfessionType::ENTREPRENEUR,
            ProfessionType::SOFTWARE_DEVELOPER,
            ProfessionType::RESEARCHER,
            ProfessionType::LINGUIST,
            ProfessionType::DATA_AI_ML,
            ProfessionType::COMMUNICATION,
        ];

        $ageRanges = [
            AgeRange::AGE_18_24,
            AgeRange::AGE_25_34,
            AgeRange::AGE_25_34,
            AgeRange::AGE_35_44,
            AgeRange::AGE_35_44,
            AgeRange::AGE_45_54,
            AgeRange::AGE_55_64,
            AgeRange::AGE_65_PLUS,
        ];

        $organizations = [
            null,
            'Indépendant',
            'Association communautaire locale',
            'Établissement scolaire',
            'Université / centre de formation',
            'Entreprise privée',
        ];

        $localities = Locality::query()
            ->whereIn('name', ['Toma', 'Tougan'])
            ->pluck('id', 'name');

        $index = 0;

        foreach ($surnames as $surname) {
            foreach ($firstNames as $firstName) {
                $index++;
                $name = $firstName.' '.$surname;
                $email = Str::slug($firstName.'.'.$surname, '.').'.'.str_pad((string) $index, 3, '0', STR_PAD_LEFT).'@example.test';

                $user = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'email_verified_at' => now()->subDays($index % 20),
                        'password' => Str::random(64),
                        'status' => UserStatus::ACTIVE,
                    ],
                );

                $user->syncRoles([UserRole::CONTRIBUTOR->value]);

                $countryCode = match (true) {
                    $index <= 82 => 'BF',
                    $index <= 88 => 'CI',
                    $index <= 93 => 'GH',
                    $index <= 97 => 'FR',
                    default => 'ML',
                };

                $profession = $professions[($index - 1) % count($professions)];
                $ageRange = $ageRanges[($index - 1) % count($ageRanges)];
                $public = $index <= 48;

                UserProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'country_code' => $countryCode,
                        'age_range' => $ageRange,
                        'profession' => $profession,
                        'profession_other' => null,
                        'organization' => $organizations[($index - 1) % count($organizations)],
                        'public_profile_enabled' => $public,
                        'public_display_name' => $public ? $name : null,
                        'public_bio' => $public
                            ? 'Je contribue à la documentation et à la transmission du San.'
                            : null,
                        'github_url' => null,
                        'linkedin_url' => null,
                        'onboarding_completed_at' => now()->subDays($index % 25),
                    ],
                );

                $localityName = $index % 2 === 0 ? 'Toma' : 'Tougan';
                $localityId = $localities[$localityName] ?? null;
                $fluency = match ($index % 10) {
                    0, 1, 2, 3 => 'native',
                    4, 5, 6 => 'fluent',
                    7, 8 => 'intermediate',
                    default => 'basic',
                };

                ContributorProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'public_code' => 'DEMO-SAN-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                        'guest_token_hash' => null,
                        'locality_id' => $localityId,
                        'locality_other' => null,
                        'fluency_level' => $fluency,
                        'can_write_san' => $index % 4 !== 0,
                        'last_seen_at' => now()->subDays($index % 14),
                    ],
                );
            }
        }

        $this->command?->info('100 contributeurs de démonstration ont été créés ou mis à jour.');
    }
}
