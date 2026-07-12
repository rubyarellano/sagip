<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\AppNotification;
use Flux\Flux;

new #[Title('Notifications')] class extends Component {
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
}; ?>

<div>
    <div class="max-w-4xl mx-auto px-4 py-4 space-y-6">
        
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    wire:click="$set('filter', 'all')" 
                    class="px-4 py-2 text-sm font-bold rounded-xl cursor-pointer transition-colors {{ $filter === 'all' ? 'bg-[#5c1d1d] text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }}"
                >
                    All
                </button>
                <button 
                    type="button" 
                    wire:click="$set('filter', 'unread')" 
                    class="px-4 py-2 text-sm font-bold rounded-xl cursor-pointer transition-colors {{ $filter === 'unread' ? 'bg-[#5c1d1d] text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }}"
                >
                    Unread 
                    @php
                        $unreadCount = auth()->user()->appNotifications()->where('is_read', false)->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="ml-1 bg-red-600 text-white text-xs font-extrabold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                    @endif
                </button>
            </div>

            <!-- Global Actions -->
            <div class="flex items-center gap-3">
                <button type="button" wire:click="markAllAsRead" class="text-sm font-bold text-neutral-600 hover:text-neutral-900 cursor-pointer">
                    Mark all as read
                </button>
                <span class="text-neutral-350">|</span>
                <button type="button" wire:click="clearAll" class="text-sm font-bold text-red-600 hover:text-red-900 cursor-pointer">
                    Clear all
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="space-y-4">
            @php
                $notificationsQuery = auth()->user()->appNotifications()->latest();
                if ($filter === 'unread') {
                    $notificationsQuery->where('is_read', false);
                }
                $notifications = $notificationsQuery->get();
            @endphp

            @forelse($notifications as $notification)
                <div class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex items-start gap-4 transition-colors {{ !$notification->is_read ? 'border-l-4 border-l-[#5c1d1d] bg-[#f9f9fa] dark:bg-zinc-850' : '' }}">
                    <!-- Left icon column -->
                    <div class="flex-shrink-0 mt-1">
                        @if($notification->type === 'success')
                            <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <flux:icon.check-circle class="h-5 w-5" />
                            </div>
                        @elseif($notification->type === 'warning')
                            <div class="h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-950/50 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                                <flux:icon.exclamation-circle class="h-5 w-5" />
                            </div>
                        @elseif($notification->type === 'danger')
                            <div class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-950/50 flex items-center justify-center text-red-600 dark:text-red-400 animate-pulse">
                                <flux:icon.exclamation-triangle class="h-5 w-5" />
                            </div>
                        @else
                            <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <flux:icon.information-circle class="h-5 w-5" />
                            </div>
                        @endif
                    </div>

                    <!-- Middle content column -->
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-center justify-between gap-4">
                            <h4 class="font-extrabold text-neutral-900 dark:text-white truncate">
                                {{ $notification->title }}
                            </h4>
                            <span class="text-xs text-neutral-450 font-medium whitespace-nowrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 leading-normal">
                            {{ $notification->body }}
                        </p>
                        
                        @if($notification->link)
                            <div class="pt-2">
                                <a href="{{ $notification->link }}" wire:navigate class="text-xs font-bold text-[#5c1d1d] hover:underline flex items-center gap-1">
                                    View Details &rarr;
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Right actions column -->
                    <div class="flex-shrink-0 flex items-center gap-2">
                        @if(!$notification->is_read)
                            <button 
                                type="button" 
                                wire:click="markAsRead({{ $notification->id }})" 
                                class="text-xs font-bold bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-3 py-1.5 rounded-lg border border-neutral-300 transition-colors cursor-pointer"
                                title="Mark as read"
                            >
                                Read
                            </button>
                        @endif
                        <button 
                            type="button" 
                            wire:click="deleteNotification({{ $notification->id }})" 
                            class="text-xs font-bold hover:bg-red-50 text-red-600 p-1.5 rounded-lg transition-colors cursor-pointer"
                            title="Delete"
                        >
                            <flux:icon.trash class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-900 rounded-3xl p-12 border border-zinc-200 dark:border-zinc-800 text-center shadow-sm">
                    <div class="h-16 w-16 mx-auto bg-neutral-100 rounded-full flex items-center justify-center text-neutral-400 mb-4">
                        <flux:icon.bell class="h-8 w-8" />
                    </div>
                    <h4 class="text-lg font-bold text-neutral-900 dark:text-white">All caught up!</h4>
                    <p class="text-sm text-neutral-500 mt-1">You have no notifications in this view.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
