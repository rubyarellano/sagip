<?php

use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use App\Enums\IncidentStatus;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentCategory;

return new #[Title('Live Map')] class extends Component {
    public ?Incident $selectedIncident = null;
    public string $selectedStatus = '';

    public function mount(): void
    {
        if (!auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }
    }

    public function viewIncident(int $id): void
    {
        $this->selectedIncident = Incident::with('user')->find($id);
        if ($this->selectedIncident) {
            $this->selectedStatus = $this->selectedIncident->status;
            Flux::modal('live-map-details-modal')->show();
        }
    }

    public function updateStatus(string $status): void
    {
        if ($this->selectedIncident) {
            $this->selectedIncident->status = $status;
            $this->selectedIncident->save();
            
            $this->selectedStatus = $status;
            Flux::toast(variant: 'success', text: __('Incident status updated to :status.', ['status' => $status]));
            
            $activeIncidents = Incident::whereIn('status', [
                IncidentStatus::Dispatched->value,
                IncidentStatus::Processing->value,
                IncidentStatus::InProgress->value
            ])->get();
            
            $this->dispatch('refresh-incidents', incidents: $activeIncidents->toArray());
        }
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/8da17d74.blade.php', $data);
    }
}; 