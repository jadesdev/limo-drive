<?php

namespace App\Actions;

use App\Events\DriverCreated;
use App\Models\Driver;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DriverAction
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    /**
     * Create a new driver with the given data.
     */
    public function create(array $data): Driver
    {
        $driver = DB::transaction(function () use ($data) {
            if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {
                $data['profile_image'] = $this->fileUploadService->upload($data['profile_image'], 'drivers/profiles')['file_path'];
            }

            if (! isset($data['status'])) {
                $data['status'] = 'active';
            }

            if (! isset($data['is_available'])) {
                $data['is_available'] = true;
            }

            return Driver::create($data);
        });
        event(new DriverCreated($driver));

        return $driver;
    }

    /**
     * Update an existing driver with the given data.
     */
    public function update(Driver $driver, array $data): Driver
    {
        return DB::transaction(function () use ($driver, $data) {
            if (isset($data['profile_image'])) {
                if ($data['profile_image'] instanceof UploadedFile) {
                    if ($driver->profile_image) {
                        $this->fileUploadService->delete($driver->profile_image);
                    }
                    $data['profile_image'] = $this->fileUploadService->upload($data['profile_image'], 'drivers/profiles')['file_path'];
                }
            }

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
            if ($driver->profile_image) {
                $this->fileUploadService->delete($driver->profile_image);
            }

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
