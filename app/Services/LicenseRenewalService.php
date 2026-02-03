<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseRenewalHistory;
use App\Models\RenewalChangeType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LicenseRenewalService
{
    /**
     * Update the license renewal date with history tracking.
     *
     * @param License $license
     * @param string|Carbon $newRenewalDate
     * @param string $changeType
     * @param string|null $reason
     * @param float|null $renewalCost
     * @param array $metadata
     * @return LicenseRenewalHistory
     */
    public function updateRenewalDate(
        License $license,
        string|Carbon $newRenewalDate,
        string $changeType = 'renewal',
        ?string $reason = null,
        ?float $renewalCost = null,
        array $metadata = []
    ): LicenseRenewalHistory {
        $newDate = $newRenewalDate instanceof Carbon
            ? $newRenewalDate
            : Carbon::parse($newRenewalDate);

        $oldRenewalDate = $license->renewal_date;
        $oldStatus = $license->status?->value;

        return DB::transaction(function () use ($license, $newDate, $oldRenewalDate, $oldStatus, $changeType, $reason, $renewalCost, $metadata) {
            // Create history record first
            $history = LicenseRenewalHistory::create([
                'license_id' => $license->id,
                'old_renewal_date' => $oldRenewalDate,
                'new_renewal_date' => $newDate,
                'change_type' => $changeType,
                'changed_by' => Auth::id(),
                'reason' => $reason,
                'old_status' => $oldStatus,
                'new_status' => $license->status?->value,
                'renewal_cost' => $renewalCost,
                'metadata' => $metadata,
                'changed_at' => now(),
            ]);

            // Update the license renewal date
            $license->update([
                'renewal_date' => $newDate,
            ]);

            return $history;
        });
    }

    /**
     * Record initial renewal date when license is created.
     *
     * @param License $license
     * @param string|null $reason
     * @return LicenseRenewalHistory|null
     */
    public function recordInitialRenewalDate(License $license, ?string $reason = null): ?LicenseRenewalHistory
    {
        if (!$license->renewal_date) {
            return null;
        }

        return LicenseRenewalHistory::create([
            'license_id' => $license->id,
            'old_renewal_date' => null,
            'new_renewal_date' => $license->renewal_date,
            'change_type' => 'initial',
            'changed_by' => Auth::id(),
            'reason' => $reason ?? 'Initial license setup',
            'old_status' => null,
            'new_status' => $license->status?->value,
            'renewal_cost' => $license->cost,
            'metadata' => [],
            'changed_at' => now(),
        ]);
    }

    /**
     * Extend the license by a specific period.
     *
     * @param License $license
     * @param int $days
     * @param string|null $reason
     * @param float|null $extensionCost
     * @return LicenseRenewalHistory
     */
    public function extendByDays(
        License $license,
        int $days,
        ?string $reason = null,
        ?float $extensionCost = null
    ): LicenseRenewalHistory {
        $currentDate = $license->renewal_date ?? Carbon::today();
        $newDate = Carbon::parse($currentDate)->addDays($days);

        return $this->updateRenewalDate(
            $license,
            $newDate,
            'extension',
            $reason ?? "Extended by {$days} days",
            $extensionCost,
            ['extension_days' => $days]
        );
    }

    /**
     * Extend the license by months.
     *
     * @param License $license
     * @param int $months
     * @param string|null $reason
     * @param float|null $extensionCost
     * @return LicenseRenewalHistory
     */
    public function extendByMonths(
        License $license,
        int $months,
        ?string $reason = null,
        ?float $extensionCost = null
    ): LicenseRenewalHistory {
        $currentDate = $license->renewal_date ?? Carbon::today();
        $newDate = Carbon::parse($currentDate)->addMonths($months);

        return $this->updateRenewalDate(
            $license,
            $newDate,
            'extension',
            $reason ?? "Extended by {$months} month(s)",
            $extensionCost,
            ['extension_months' => $months]
        );
    }

    /**
     * Correct a renewal date (for fixing errors).
     *
     * @param License $license
     * @param string|Carbon $correctDate
     * @param string $reason
     * @return LicenseRenewalHistory
     */
    public function correctRenewalDate(
        License $license,
        string|Carbon $correctDate,
        string $reason
    ): LicenseRenewalHistory {
        return $this->updateRenewalDate(
            $license,
            $correctDate,
            'correction',
            $reason,
            null,
            ['correction' => true]
        );
    }

    /**
     * Get renewal statistics for a license.
     *
     * @param License $license
     * @return array
     */
    public function getRenewalStatistics(License $license): array
    {
        $histories = $license->renewalHistories;

        return [
            'total_renewals' => $histories->where('change_type', 'renewal')->count(),
            'total_extensions' => $histories->where('change_type', 'extension')->count(),
            'total_corrections' => $histories->where('change_type', 'correction')->count(),
            'total_cost' => $histories->sum('renewal_cost'),
            'first_renewal_date' => $histories->last()?->new_renewal_date,
            'latest_renewal_date' => $histories->first()?->new_renewal_date,
            'history_count' => $histories->count(),
        ];
    }
}
