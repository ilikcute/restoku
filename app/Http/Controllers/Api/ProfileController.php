<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\UserResource;
use App\Interfaces\ProfileRepositoryInterface;
use Illuminate\Http\Request;

class ProfileController extends BaseApiController
{
    protected ProfileRepositoryInterface $profileRepository;

    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function show(Request $request)
    {
        $user = $this->profileRepository->getProfile($request->user());

        return $this->successResponse(new UserResource($user));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $this->profileRepository->updateProfile(
            $request->user(),
            $request->validated(),
            $request->hasFile('avatar') ? $request->file('avatar') : null
        );

        return $this->successResponse(new UserResource($user), 'Profile updated successfully');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $this->profileRepository->updatePassword(
            $request->user(),
            $request->validated()['password']
        );

        return $this->successResponse(null, 'Password updated successfully');
    }
}
