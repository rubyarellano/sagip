<?php

use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Carbon\Carbon;
use App\Enums\IncidentStatus;

return new #[Title('Reports History')] class extends Component {
    public string $search = '';
    public string $category = '';
    public string $status = '';
    public int $page = 1;
    public int $perPage = 10;
    
    public ?Incident $selectedIncident = null;

    public function mount(): void
    {
        if (auth()->user()->isAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
            return;
        }
    }

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

    public function viewIncident(int $id): void
    {
        $this->selectedIncident = Incident::where('user_id', auth()->id())->find($id);
        if ($this->selectedIncident) {
            Flux::modal('citizen-history-details-modal')->show();
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
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/a499d7f7.blade.php', $data);
    }
}; 