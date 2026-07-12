<?php

use App\Models\User;
use App\Models\Incident;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;
use Carbon\Carbon;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;

return new #[Title('Admin Dashboard')] class extends Component {
    public ?Incident $selectedIncident = null;
    public string $selectedStatus = '';

    // Charts data properties
    public array $chartData = [];
    public array $chartCategories = [];
    public array $categoryChartData = [];
    public array $categoryChartLabels = [];

    public function mount(): void
    {
        if (!auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }

        $this->loadChartData();
    }

    public function loadChartData(): void
    {
        // 1. Alert trends for the past 7 days (pre-populated with 0s)
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[$date] = 0;
        }

        $trends = Incident::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->get();

        foreach ($trends as $trend) {
            if (isset($days[$trend->date])) {
                $days[$trend->date] = $trend->count;
            }
        }

        $this->chartData = array_values($days);
        $this->chartCategories = array_map(fn($d) => Carbon::parse($d)->format('M d'), array_keys($days));

        // 2. Incident categories breakdown
        $categoriesBreakdown = Incident::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get();

        $this->categoryChartData = $categoriesBreakdown->pluck('count')->toArray();
        $this->categoryChartLabels = $categoriesBreakdown->pluck('category')->toArray();
    }

    public function viewIncident(int $id): void
    {
        $this->selectedIncident = Incident::with('user')->find($id);
        if ($this->selectedIncident) {
            $this->selectedStatus = $this->selectedIncident->status;
            Flux::modal('incident-details-modal')->show();
        }
    }

    public function updateStatus(string $status): void
    {
        if ($this->selectedIncident) {
            $this->selectedIncident->status = $status;
            $this->selectedIncident->save();
            
            $this->selectedStatus = $status;
            Flux::toast(variant: 'success', text: __('Incident status updated to :status.', ['status' => $status]));
            
            $this->loadChartData();
            $this->dispatch('refresh-charts', chartData: $this->chartData, chartCategories: $this->chartCategories, categoryChartData: $this->categoryChartData, categoryChartLabels: $this->categoryChartLabels);
        }
    }

    public function dispatchUnit(int $incidentId): void
    {
        $incident = Incident::find($incidentId);
        if ($incident) {
            $incident->status = IncidentStatus::Dispatched->value;
            $incident->save();
            
            Flux::toast(variant: 'success', text: __('Emergency Unit successfully dispatched to :address!', ['address' => $incident->address]));
            
            $this->loadChartData();
            $this->dispatch('refresh-charts', chartData: $this->chartData, chartCategories: $this->chartCategories, categoryChartData: $this->categoryChartData, categoryChartLabels: $this->categoryChartLabels);
        }
    }



    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/b3c29102.blade.php', $data);
    }
}; 