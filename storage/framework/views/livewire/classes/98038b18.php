<?php

use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

return new #[Title('Registered Users')] class extends Component {
    public string $search = '';
    public string $locationFilter = '';
    public int $page = 1;
    public int $perPage = 10;

    // View Details Modal State
    public ?User $selectedUser = null;

    // Edit Modal State
    public ?User $editingUser = null;
    public string $editFirstName = '';
    public string $editLastName = '';
    public string $editPhone = '';
    public string $editAddress = '';
    public string $editBloodType = '';
    public string $editHeight = '';
    public string $editWeight = '';
    public string $editAllergies = '';

    // Delete Confirmation Modal State
    public ?int $deletingUserId = null;

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedLocationFilter(): void
    {
        $this->page = 1;
    }

    public function viewUser(int $id): void
    {
        $this->selectedUser = User::with('emergencyContacts')->find($id);
        if ($this->selectedUser) {
            Flux::modal('view-user-modal')->show();
        }
    }

    public function editUser(int $id): void
    {
        $this->editingUser = User::find($id);
        if ($this->editingUser) {
            $this->editFirstName = $this->editingUser->first_name ?? '';
            $this->editLastName = $this->editingUser->last_name ?? '';
            $this->editPhone = $this->editingUser->phone ?? '';
            $this->editAddress = $this->editingUser->address ?? '';
            $this->editBloodType = $this->editingUser->blood_type ?? '';
            $this->editHeight = $this->editingUser->height ?? '';
            $this->editWeight = $this->editingUser->weight ?? '';
            $this->editAllergies = $this->editingUser->allergies ?? '';
            
            Flux::modal('view-user-modal')->close();
            Flux::modal('edit-user-modal')->show();
        }
    }

    public function updateUser(): void
    {
        if ($this->editingUser) {
            $validated = $this->validate([
                'editFirstName' => ['required', 'string', 'max:255'],
                'editLastName' => ['required', 'string', 'max:255'],
                'editPhone' => ['nullable', 'string', 'max:20'],
                'editAddress' => ['nullable', 'string', 'max:500'],
                'editBloodType' => ['nullable', 'string', 'max:10'],
                'editHeight' => ['nullable', 'string', 'max:20'],
                'editWeight' => ['nullable', 'string', 'max:20'],
                'editAllergies' => ['nullable', 'string', 'max:1000'],
            ]);

            $this->editingUser->update([
                'first_name' => $validated['editFirstName'],
                'last_name' => $validated['editLastName'],
                'name' => $validated['editFirstName'] . ' ' . $validated['editLastName'],
                'phone' => $validated['editPhone'],
                'address' => $validated['editAddress'],
                'blood_type' => $validated['editBloodType'],
                'height' => $validated['editHeight'],
                'weight' => $validated['editWeight'],
                'allergies' => $validated['editAllergies'],
            ]);

            Flux::modal('edit-user-modal')->close();
            Flux::toast(variant: 'success', text: __('User profile updated.'));
        }
    }

    public function confirmDeleteUser(int $id): void
    {
        $this->deletingUserId = $id;
        Flux::modal('delete-confirm-modal')->show();
    }

    public function deleteUser(): void
    {
        if ($this->deletingUserId) {
            $user = User::find($this->deletingUserId);
            if ($user) {
                // This triggers Eloquent deleting event (which returns false if role == 'citizen')
                $deleted = $user->delete();
                if ($deleted) {
                    Flux::toast(variant: 'success', text: __('User deleted successfully.'));
                }
            }
            $this->deletingUserId = null;
            Flux::modal('delete-confirm-modal')->close();
        }
    }

    public function nextPage(int $total): void
    {
        if ($this->page * $this->perPage < $total) {
            $this->page++;
        }
    }

    public function prevPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    protected function validationAttributes(): array
    {
        return [
            'editFirstName' => 'first name',
            'editLastName' => 'last name',
            'editBloodType' => 'blood type',
        ];
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/98038b18.blade.php', $data);
    }
}; 