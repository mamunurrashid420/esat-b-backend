<?php

namespace App\Console\Commands;

use App\Models\User;
use App\PrimaryMemberType;
use App\UserRole;
use Illuminate\Console\Command;

class DisableExpiredMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:disable-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable members whose membership_expires_at has passed (GENERAL/ASSOCIATE only). Run daily so duration auto becomes inactive.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = User::query()
            ->where('role', UserRole::Member)
            ->whereNull('disabled_at')
            ->whereNotNull('membership_expires_at')
            ->whereIn('primary_member_type', [PrimaryMemberType::General, PrimaryMemberType::Associate])
            ->where('membership_expires_at', '<', now());

        $count = $query->count();

        if ($count === 0) {
            $this->info('No expired members to disable.');

            return self::SUCCESS;
        }

        $query->update(['disabled_at' => now()]);

        $this->info("Disabled {$count} member(s) whose membership duration had expired.");

        return self::SUCCESS;
    }
}
