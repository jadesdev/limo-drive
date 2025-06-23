<?php

namespace App\Actions;

use App\Models\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverAction
{
    /**
     * Create a new driver with the given data.
     */
    public function create(array $data): Driver
    {
        return DB::transaction(function () use ($data) {
            // Handle profile image upload
            if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {
                $data['profile_image'] = $this->uploadFile($data['profile_image'], 'drivers/profiles');
            }

            // Set default status if not provided
            if (! isset($data['status'])) {
                $data['status'] = 'active';
            }

            // Set default is_available if not provided
            if (! isset($data['is_available'])) {
                $data['is_available'] = true;
            }

            // Create the driver
            return Driver::create($data);
        });
    }

    /**
     * Update an existing driver with the given data.
     */
    public function update(Driver $driver, array $data): Driver
    {
        return DB::transaction(function () use ($driver, $data) {
            // Handle profile image update
            if (isset($data['profile_image'])) {
                if ($data['profile_image'] instanceof UploadedFile) {
                    // Delete old file if exists
                    if ($driver->profile_image) {
                        Storage::disk('uploads')->delete($driver->profile_image);
                    }
                    $data['profile_image'] = $this->uploadFile($data['profile_image'], 'drivers/profiles');
                } elseif ($data['profile_image'] === null) {
                    // Remove the profile image
                    if ($driver->profile_image) {
                        Storage::disk('uploads')->delete($driver->profile_image);
                        $data['profile_image'] = null;
                    }
                }
            }

            // Update the driver
            $driver->update($data);

            return $driver->fresh();
        });
    }

    /**
     * Delete a driver and their associated files.
     */
    public function delete(Driver $driver): bool
    {
        return DB::transaction(function () use ($driver) {
            // Delete associated files
            if ($driver->profile_image) {
                Storage::disk('uploads')->delete($driver->profile_image);
            }

            // Delete the driver (soft delete)
            return $driver->delete();
        });
    }

    /**
     * Update driver's location.
     */
    public function updateLocation(Driver $driver, float $latitude, float $longitude): bool
    {
        return $driver->update([
            'current_lat' => $latitude,
            'current_lng' => $longitude,
            'last_online_at' => now(),
        ]);
    }

    /**
     * Update driver's availability.
     */
    public function updateAvailability(Driver $driver, bool $isAvailable): bool
    {
        return $driver->update(['is_available' => $isAvailable]);
    }

    /**
     * Upload a file to the specified directory.
     */
    protected function uploadFile(UploadedFile $file, string $directory): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'uploads');
    }

    /**
     * Get all active and available drivers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    /**
     * Get all active and available drivers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableDrivers()
    {
        return Driver::where('status', 'active')
            ->where('is_available', true)
            ->get();
    }

    /**
     * Get drivers near a specific location.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDriversNearLocation(float $latitude, float $longitude, int $radiusInKm = 10)
    {
        $haversine = "(6371 * acos(cos(radians($latitude)) 
                     * cos(radians(current_lat)) 
                     * cos(radians(current_lng) 
                     - radians($longitude)) 
                     + sin(radians($latitude)) 
                     * sin(radians(current_lat))))";

        return Driver::selectRaw("*, $haversine AS distance")
            ->whereRaw("$haversine < ?", [$radiusInKm])
            ->orderBy('distance')
            ->get();
    }
}
