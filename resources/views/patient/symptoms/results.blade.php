<x-layouts.dashboard>
    <div class="container py-4">
        <h2 class="fw-bold mb-3"><i class="bi bi-robot text-primary"></i> Symptom Analyzer Results</h2>

        <div class="alert alert-info mt-4">
            <strong>Based on your symptoms:</strong> "{{ $symptomsStr }}"<br>
            <strong>We recommend the following specialists:</strong>
            {{ implode(', ', $matchedSpecializations) }}
        </div>

        <h4 class="mt-4 mb-3 fw-bold">Recommended Doctors</h4>
        @if($doctors->isEmpty())
            <div class="alert alert-warning border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill text-warning"></i>
                No doctors available for these specializations at the moment. Please check back later.</div>
        @else
            <div class="row mt-3">
                                @php
                                    $resDocName = $doctor->name;
                                    if (!str_starts_with(strtolower($resDocName), 'dr.')) {
                                        $resDocName = 'Dr. ' . $resDocName;
                                    }
                                @endphp
                                <h5 class="card-title fw-bold text-primary">{{ $resDocName }}</h5>
                                <p class="card-text text-muted mb-2">
                                    <i class="bi bi-hospital"></i> {{ $doctor->doctorProfile->hospital_name ?? 'MedFlow' }}
                                    @if(isset($doctor->hospital_distance))
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $doctor->hospital_lat }},{{ $doctor->hospital_lng }}" target="_blank" class="text-decoration-none">
                                            <span class="badge bg-success text-white ms-1 hover-shadow transition"><i class="bi bi-geo-alt-fill"></i> {{ $doctor->hospital_distance }} km away</span>
                                        </a>
                                    @endif
                                </p>
                                <div class="live-status-container my-2 p-2 border rounded bg-light" data-doctor-id="{{ $doctor->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="fw-bold text-dark">Live Status:</small>
                                        <span class="badge bg-secondary live-status-badge">Loading...</span>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">Serving Token: <strong class="text-primary live-token-badge">...</strong></small>
                                    </div>
                                </div>
                                <p class="card-text fw-bold text-dark mt-2"><i class="bi bi-heart-pulse text-danger"></i>
                                    {{ $doctor->specialty }}</p>
                                <a href="{{ route('patient.book.create.step.one', ['doctor' => $doctor->id]) }}"
                                    class="btn btn-success w-100 mt-2 rounded-pill fw-bold"><i class="bi bi-calendar-check"></i>
                                    Book Appointment</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('patient.symptoms.index') }}" class="btn btn-outline-secondary rounded-pill"><i
                    class="bi bi-arrow-left"></i> Analyze Different Symptoms</a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const liveContainers = document.querySelectorAll('.live-status-container');
            
            async function fetchLiveStatuses() {
                liveContainers.forEach(async (container) => {
                    const doctorId = container.dataset.doctorId;
                    try {
                        const res = await fetch(`/patient/api/doctors/${doctorId}/live-status`);
                        const data = await res.json();
                        
                        const statusBadge = container.querySelector('.live-status-badge');
                        const tokenBadge = container.querySelector('.live-token-badge');
                        
                        tokenBadge.textContent = '#' + data.current_token;
                        statusBadge.textContent = data.live_status;
                        
                        if(data.live_status === 'Available') { statusBadge.className = 'badge bg-success live-status-badge'; }
                        else if(data.live_status === 'Lunch Break') { statusBadge.className = 'badge bg-warning text-dark live-status-badge'; }
                        else if(data.live_status === 'In Surgery') { statusBadge.className = 'badge bg-danger live-status-badge'; }
                        else { statusBadge.className = 'badge bg-secondary live-status-badge'; }
                    } catch (e) { console.error('Error polling status'); }
                });
            }
            
            fetchLiveStatuses();
            setInterval(fetchLiveStatuses, 10000);
        });
    </script>
    @endpush
</x-layouts.dashboard>