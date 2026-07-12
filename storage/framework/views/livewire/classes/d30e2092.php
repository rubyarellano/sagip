<?php

use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Carbon\Carbon;
use App\Enums\IncidentStatus;
use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;

return new #[Title('Alert History')] class extends Component {
    public string $search = '';
    public string $category = '';
    public string $status = '';
    public string $dateRange = '';
    public int $page = 1;
    public int $perPage = 10;
    
    public ?Incident $selectedIncident = null;
    public string $selectedStatus = '';

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedCategory(): void
    {
        $this->page = 1;
    }

    public function updatedStatus(): void
    {
        $this->page = 1;
    }

    public function updatedDateRange(): void
    {
        $this->page = 1;
    }

    public function viewIncident(int $id): void
    {
        $this->selectedIncident = Incident::with('user')->find($id);
        if ($this->selectedIncident) {
            $this->selectedStatus = $this->selectedIncident->status;
            Flux::modal('history-details-modal')->show();
        }
    }

    public function updateStatus(string $status): void
    {
        if ($this->selectedIncident) {
            $this->selectedIncident->status = $status;
            $this->selectedIncident->save();
            
            $this->selectedStatus = $status;
            Flux::toast(variant: 'success', text: __('Incident status updated to :status.', ['status' => $status]));
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

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/d30e2092.blade.php', $data);
    }
}; 