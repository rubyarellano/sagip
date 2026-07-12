<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

return new #[Title('Profile Settings')] class extends Component {
    use PasswordValidationRules;
    use ProfileValidationRules;
    use WithFileUploads;

    public $avatarFile = null;

    // Profile details properties
    public string $name = '';
    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public string $address = '';
    public string $gender = '';
    public string $birthday = '';
    public string $blood_type = '';
    public string $height = '';
    public string $weight = '';
    public string $allergies = '';

    // New emergency contact properties
    public string $new_contact_name = '';
    public string $new_contact_relation = '';
    public string $new_contact_phone = '';

    // Password Update properties
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // 2FA properties
    public bool $canManageTwoFactor;
    public bool $twoFactorEnabled;
    public bool $requiresConfirmation;

    // Passkeys properties
    #[Locked]
    public bool $canManagePasskeys;

    #[Locked]
    public array $passkeys = [];

    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deletingPasskeyId = null;

    #[Locked]
    public string $deletingPasskeyName = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
        $this->gender = $user->gender ?? '';
        $this->birthday = $user->birthday ? Carbon::parse($user->birthday)->format('Y-m-d') : '';
        $this->blood_type = $user->blood_type ?? '';
        $this->height = $user->height ?? '';
        $this->weight = $user->weight ?? '';
        $this->allergies = $user->allergies ?? '';

        // Initialize 2FA
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();
        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null($user->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication($user);
            }
            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        // Initialize Passkeys
        $this->canManagePasskeys = Features::canManagePasskeys();
        if ($this->canManagePasskeys) {
            $this->loadPasskeys();
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        if (empty($this->first_name) && !empty($this->name)) {
            $parts = explode(' ', $this->name, 2);
            $this->first_name = $parts[0];
            $this->last_name = $parts[1] ?? $parts[0];
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'string', 'max:20'],
            'birthday' => ['nullable', 'date'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'height' => ['nullable', 'string', 'max:20'],
            'weight' => ['nullable', 'string', 'max:20'],
            'allergies' => ['nullable', 'string', 'max:1000'],
            'avatarFile' => ['nullable', 'image', 'max:2048'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'gender' => $validated['gender'],
            'birthday' => $validated['birthday'],
            'blood_type' => $validated['blood_type'],
            'height' => $validated['height'],
            'weight' => $validated['weight'],
            'allergies' => $validated['allergies'],
        ];

        if ($this->avatarFile) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = $this->avatarFile->store('avatars', 'public');
        }

        $user->fill($updateData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->avatarFile = null;

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Add emergency contact.
     */
    public function addEmergencyContact(): void
    {
        $this->validate([
            'new_contact_name' => 'required|string|max:255',
            'new_contact_relation' => 'required|string|max:100',
            'new_contact_phone' => 'required|string|max:50',
        ]);

        auth()->user()->emergencyContacts()->create([
            'name' => $this->new_contact_name,
            'relation' => $this->new_contact_relation,
            'phone' => $this->new_contact_phone,
        ]);

        $this->reset('new_contact_name', 'new_contact_relation', 'new_contact_phone');

        Flux::toast(variant: 'success', text: __('Emergency contact added.'));
    }

    /**
     * Delete emergency contact.
     */
    public function deleteEmergencyContact(int $id): void
    {
        $contact = auth()->user()->emergencyContacts()->findOrFail($id);
        $contact->delete();

        Flux::toast(variant: 'success', text: __('Emergency contact removed.'));
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        Flux::toast(variant: 'success', text: __('Password updated.'));
    }

    /**
     * Load the user's passkeys.
     */
    public function loadPasskeys(): void
    {
        $this->passkeys = auth()->user()->passkeys()
            ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
            ->latest()
            ->get()
            ->map(fn ($passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at_diff' => $passkey->created_at->diffForHumans(),
                'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Show the delete confirmation modal.
     */
    public function confirmDelete(int $passkeyId): void
    {
        $passkey = auth()->user()->passkeys()->findOrFail($passkeyId);
        $this->deletingPasskeyId = $passkey->id;
        $this->deletingPasskeyName = $passkey->name;
        $this->showDeleteModal = true;
    }

    /**
     * Delete the passkey.
     */
    public function deletePasskey(DeletePasskey $deletePasskey): void
    {
        if (! $this->deletingPasskeyId) {
            return;
        }

        $passkey = auth()->user()->passkeys()->findOrFail($this->deletingPasskeyId);
        $deletePasskey(auth()->user(), $passkey);

        $this->closeDeleteModal();
        $this->loadPasskeys();
    }

    /**
     * Close the delete confirmation modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPasskeyId = null;
        $this->deletingPasskeyName = '';
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());
        $this->twoFactorEnabled = false;
    }

    protected function validationAttributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'blood_type' => 'blood type',
            'new_contact_name' => 'emergency contact name',
            'new_contact_phone' => 'emergency contact phone',
            'new_contact_relation' => 'relation',
        ];
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/ab80bfb6.blade.php', $data);
    }
}; 