<?php

namespace Modules\User\Services;

use Modules\User\Models\User;

class NotificationPereferenceService
{
    public function getPreferences(User $user)
    {
        return cache()->remember("user:preferences:{$user->id}", 60, function () use ($user) {
            $defaultPreferences = config('user.notifications.default', []);

            $userPreferences = $user->notificationPreference?->preferences ?? [];

            return collect($defaultPreferences)->map(function ($channel, $type) use ($userPreferences) {
                return collect($channel)->merge($userPreferences[$type] ?? [])->toArray();
            });
        });

    }

    public function update(User $user, string $type, string $channel, bool $value)
    {
        $userPreferences = $user->notificationPreference()->firstOrCreate();
        $defaultPreferences = config('user.notifications.default', []);
        $defaultPreferenceValue = $defaultPreferences[$type][$channel] ?? null;

        $preferences = collect($userPreferences->preferences ?? []);
        $channels = collect($preferences->get($type, []));

        if ($defaultPreferenceValue === $value) {
            $channels->forget($channel);

            if ($channels->isEmpty()) {
                $preferences->forget($type);
            } else {
                $preferences->put($type, $channels->toArray());
            }
        } else {
            $channels->put($channel, $value);
            $preferences->put($type, $channels);
        }

        $userPreferences->update([
            'preferences' => $preferences->toArray(),
        ]);
    }

    public function allowedChannels(User $user, string $type, ?array $forceChannels = []): array
    {
        $preferences = $this->getPreferences($user);
        $channels = config('user.notifications.channels', []);

        return collect($channels)
            ->filter(fn ($_, $key) => $preferences[$type][$key] ?? false)
            ->values()
            ->merge($forceChannels ?? [])
            ->toArray();
    }
}
