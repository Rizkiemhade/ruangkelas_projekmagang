<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateAdminProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));
        $oldPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');

            if (! $newPhotoPath) {
                throw new RuntimeException('Foto profil gagal disimpan.');
            }

            $oldPhotoPath = $user->profile_photo_path;
            $user->profile_photo_path = $newPhotoPath;
        }

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        if ($oldPhotoPath && Storage::disk('public')->exists($oldPhotoPath) && ! Storage::disk('public')->delete($oldPhotoPath)) {
            throw new RuntimeException('Foto profil lama gagal dihapus dari penyimpanan.');
        }

        return to_route('admin.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
