<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    public function Signature()
    {
        $user = Auth::user();
        return view('profile.signature', compact('user'));
    }

    public function UpdateSignature(Request $request)
    {
        $user = Auth::user();
        $signature_type = $request->signature_type;

        if ($signature_type === 'upload' && $request->hasFile('signature_file')) {
            $request->validate([
                'signature_file' => 'required|image|max:2048',
            ]);

            $file = $request->file('signature_file');
            $image = Image::make($file)->encode('webp', 90);
            $filename = 'signatures/user_' . $user->id . '_' . time() . '.webp';
            Storage::disk('public')->put($filename, $image);

            $user->update([
                'signature' => $filename,
                'signature_is_image' => true,
            ]);

            return redirect()->route('profile.signature')
                ->with('success', 'บันทึกลายเซ็นเรียบร้อยแล้ว');
        } elseif ($signature_type === 'draw' && $request->signature_data) {
            $signature_data = $request->signature_data;

            if (preg_match('/^data:image\/(\w+);base64,/', $signature_data)) {
                $image = Image::make($signature_data)->encode('webp', 90);
                $filename = 'signatures/user_' . $user->id . '_' . time() . '.webp';
                Storage::disk('public')->put($filename, $image);

                $user->update([
                    'signature' => $filename,
                    'signature_is_image' => true,
                ]);

                return redirect()->route('profile.signature')
                    ->with('success', 'บันทึกลายเซ็นเรียบร้อยแล้ว');
            }
        }

        return redirect()->route('profile.signature')
            ->with('error', 'ไม่สามารถบันทึกลายเซ็นได้ กรุณาลองใหม่');
    }

    public function DeleteSignature()
    {
        $user = Auth::user();

        if ($user->signature && $user->signature_is_image) {
            Storage::disk('public')->delete($user->signature);
        }

        $user->update([
            'signature' => null,
            'signature_is_image' => false,
        ]);

        return redirect()->route('profile.signature')
            ->with('success', 'ลบลายเซ็นเรียบร้อยแล้ว');
    }

    public function UpdateContact(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('profile.signature')
            ->with('success', 'บันทึกข้อมูลติดต่อเรียบร้อยแล้ว');
    }

    /**
     * Get current user profile data for editing
     */
    public function GetProfileData()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'nickname' => $user->nickname,
                'title_prefix' => $user->title_prefix,
                'gender' => $user->gender,
                'birth_date' => $user->birth_date ? $user->birth_date->format('Y-m-d') : null,
                'nationality' => $user->nationality,
                'phone' => $user->phone,
                'address' => $user->address,
                'position' => $user->position,
                'department' => $user->department,
                'bank_name' => $user->bank_name,
                'bank_account' => $user->bank_account,
            ]
        ]);
    }

    /**
     * Update current user profile
     */
    public function UpdateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            // Personal info
            'nickname' => 'nullable|string|max:100',
            'title_prefix' => 'nullable|string|max:50',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            // Bank info (user can update their own bank details)
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
        ]);

        $data = $request->only([
            'name',
            'email',
            'nickname',
            'title_prefix',
            'gender',
            'birth_date',
            'nationality',
            'phone',
            'address',
            'bank_name',
            'bank_account'
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตข้อมูลเรียบร้อยแล้ว'
        ]);
    }
}
