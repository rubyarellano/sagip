<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\AppNotification;
use Flux\Flux;

return new #[Title('Notifications')] class extends Component {
    public string $filter = 'all'; // all, unread

    public function mount(): void
    {
        if (auth()->user()->isAdmin()) {
            $this->redirect(route('admin.notifications'), navigate: true);
            return;
        }
    }

    public function markAsRead(int $id): void
    {
        $notification = auth()->user()->appNotifications()->findOrFail($id);
        $notification->update(['is_read' => true]);
        Flux::toast(variant: 'success', text: __('Marked as read.'));
    }

    public function deleteNotification(int $id): void
    {
        $notification = auth()->user()->appNotifications()->findOrFail($id);
        $notification->delete();
        Flux::toast(variant: 'success', text: __('Notification deleted.'));
    }

    public function markAllAsRead(): void
    {
        auth()->user()->appNotifications()->where('is_read', false)->update(['is_read' => true]);
        Flux::toast(variant: 'success', text: __('All notifications marked as read.'));
    }

    public function clearAll(): void
    {
        auth()->user()->appNotifications()->delete();
        Flux::toast(variant: 'success', text: __('Notifications cleared.'));
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/d21e88db.blade.php', $data);
    }
}; 