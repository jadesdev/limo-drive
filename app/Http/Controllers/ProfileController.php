<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

#[Group('Admin Profile')]
class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get Admin Details
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->dataResponse('User Details', $user);
    }

    /**
     * Update Admin
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:255',
            'address' => 'sometimes|array',
        ]);

        $user = $request->user();
        $user->update($validated);

        return $this->successResponse('User updated successfully', $user);
    }

    /**
     * Upload Image
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();
        $user->image = $request->file('image')->store('images', 'uploads');
        $user->save();

        return $this->successResponse('Image uploaded successfully', $user);
    }

    /**
     * Change password
     */
    public function passwordUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return $this->successResponse('Password updated successfully');
    }
}
