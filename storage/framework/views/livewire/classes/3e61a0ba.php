<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Incident;
use Flux\Flux;
use App\Enums\IncidentCategory;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Validation\Rules\Enum;

return new #[Title('Report Incident')] class extends Component {
    use WithFileUploads;

    public string $category = 'Flood'; // Flood selected by default in Figma. Keep string type to bind to UI seamlessly
    public string $severity = 'Medium';
    public float $latitude = 12.9644;
    public float $longitude = 124.0044;
    public string $address = 'Brgy. 10, Sorsogon City';
    
    // Step 3 details
    public string $status = 'In Danger';
    public string $description = '';

    // Step 4 photo upload
    public $photo;

    // Step 5 contacts
    public string $reporterName = '';
    public string $reporterPhone = '';

    public function mount(): void
    {
        if (Auth::user()->isAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
            return;
        }

        $this->reporterName = Auth::user()->name;
        $this->reporterPhone = Auth::user()->phone ?? '';
    }

    public function selectCategory(string $category)
    {
        $this->category = $category;

        // Auto-assign severity based on category importance
        if (in_array($category, [IncidentCategory::Fire->value, IncidentCategory::Medical->value, IncidentCategory::Earthquake->value])) {
            $this->severity = IncidentSeverity::High->value;
        } else {
            $this->severity = IncidentSeverity::Medium->value;
        }
    }

    public function submitReport()
    {
        $this->validate([
            'category' => ['required', new Enum(IncidentCategory::class)],
            'severity' => ['required', new Enum(IncidentSeverity::class)],
            'address' => 'required|string',
            'status' => 'required|string',
            'description' => 'required|string|min:5',
            'reporterName' => 'required|string|max:255',
            'reporterPhone' => 'required|string|max:50',
            'photo' => 'nullable|image|max:10240', // 10MB Max
        ]);

        $photoPath = null;
        if ($this->photo) {
            $photoPath = $this->photo->store('incidents', 'public');
        }

        // Map status input to database status
        $dbStatus = ($this->status === 'In Danger') ? IncidentStatus::Dispatched->value : IncidentStatus::Processing->value;

        Incident::create([
            'user_id' => Auth::id(),
            'category' => $this->category,
            'severity' => $this->severity,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'status' => $dbStatus,
            'description' => $this->description,
            'photo_path' => $photoPath,
            'reporter_name' => $this->reporterName,
            'reporter_phone' => $this->reporterPhone,
        ]);

        Flux::toast(variant: 'success', text: __('Incident report submitted successfully!'));
        $this->redirect(route('reports-history'), navigate: true);
    }

    protected function view($data = [])
    {
        return app('view')->file('E:\Herd\sagip\storage\framework\views/livewire/views/3e61a0ba.blade.php', $data);
    }
}; 