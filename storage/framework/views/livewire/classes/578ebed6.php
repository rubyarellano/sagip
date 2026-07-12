<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\EmergencyContact;
use Flux\Flux;

return new #[Title('Emergency Contacts')] class extends Component {
    public string $name = '';
    public string $relation = '';
    public string $phone = '';

    public function mount(): void
    {
        if (Auth::user()->isAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
            return;
        }
    }

    public function saveContact()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'relation' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
        ]);

        EmergencyContact::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'relation' => $this->relation,
            'phone' => $this->phone,
        ]);

        $this->name = '';
        $this->relation = '';
        $this->phone = '';

        Flux::toast(variant: 'success', text: __('Emergency contact added successfully!'));
    }

    public function deleteContact(int $id): void
    {
        $contact = EmergencyContact::where('user_id', Auth::id())->findOrFail($id);
        $contact->delete();

        Flux::toast(variant: 'success', text: __('Emergency contact removed successfully!'));
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/578ebed6.blade.php', $data);
    }
}; 