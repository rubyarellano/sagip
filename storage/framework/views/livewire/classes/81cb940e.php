<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

return new #[Title('Dashboard')] class extends Component {
    public string $deviceId = '';
    public int $step = 1;
    public string $statusText = '';
    public bool $isVerified = false;

    public function mount(): void
    {
        if (Auth::user()->isAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
            return;
        }

        $this->deviceId = Auth::user()->device_id ?? '';
    }

    public function openRegisterWatch()
    {
        $this->step = 1;
        $this->isVerified = false;
        $this->statusText = '';
        Flux::modal('register-watch-modal')->show();
    }

    public function verifyDevice()
    {
        if (empty($this->deviceId)) {
            $this->addError('deviceId', 'Unique Device ID is required.');
            return;
        }

        $this->isVerified = true;
        $this->step = 2;
        $this->statusText = 'Verifying Token: OK';
    }

    public function pairDevice()
    {
        $user = Auth::user();
        $user->device_id = $this->deviceId;
        $user->save();

        Flux::modal('register-watch-modal')->close();
        Flux::toast(variant: 'success', text: __('Watch registered and paired successfully!'));
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/81cb940e.blade.php', $data);
    }
}; 