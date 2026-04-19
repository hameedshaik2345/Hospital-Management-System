<x-layouts.dashboard>
    @include('patient.book.partials.header', ['step' => 2])

    <form id="doctor-selection-form" method="POST" action="{{ route('patient.book.store.step.two') }}">
        @csrf
        
        <div class="card mb-4 border-primary shadow-sm" style="border-radius: 12px; border-width: 2px;">
            <div class="row align-items-center g-3 p-2">
                <div class="col-md-5">
                    <h3 class="h5 fw-bold mb-1">1. Select Hospital</h3>
                    <p class="text-muted small mb-0">Choose a location to see doctors.</p>
                    <select id="hospital-select" class="form-select mt-2" style="border-radius: 8px;">
                        <option value="all">All Hospitals</option>
                        @foreach($hospitals as $hospital)
                            <option value="{{ $hospital }}">{{ $hospital }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <h3 class="h5 fw-bold mb-1">Search Doctor</h3>
                    <p class="text-muted small mb-0">Filter by name or specialty.</p>
                    <div class="input-group mt-2">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;"><i class="bi bi-search"></i></span>
                        <input type="text" id="doctor-search" class="form-control border-start-0" placeholder="Type doctor name or specialty (e.g. Heart)..." style="border-radius: 0 8px 8px 0;">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm" style="border-radius: 12px;">
            <h3 class="h5 fw-bold mb-2">2. Select Doctor</h3>
            <p class="text-muted">Choose your preferred healthcare provider from the selected hospital.</p>
            <div class="row g-3 mt-3" id="doctors-list">
                @foreach($doctors as $doctor)
                @php $doctorHospital = $doctor->doctorProfile->hospital_name ?? 'General Hospital'; @endphp
                <div class="col-md-6 doctor-item" data-hospital="{{ $doctorHospital }}">
                    <label class="d-block card-radio">
                        <input type="radio" name="doctor_id" value="{{ $doctor->id }}" class="d-none doctor-radio" required>
                        <div class="card card-body h-100" style="border-radius: 10px; transition: all 0.2s;">
                            <h5 class="fw-bold">{{ $doctor->name }}</h5>
                            <p class="mb-1 fw-bold" style="color:#0066CC;">{{ $doctor->specialty }}</p>
                            <p class="card-text text-muted mb-2"><i class="bi bi-hospital"></i>
                                {{ $doctorHospital }}
                                @if(isset($doctor->hospital_lat) && isset($doctor->hospital_lng))
                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $doctor->hospital_lat }},{{ $doctor->hospital_lng }}" target="_blank" class="text-decoration-none">
                                        <span class="badge bg-success ms-1 hover-shadow transition">
                                            <i class="bi bi-geo-alt-fill"></i> 
                                            @if(isset($doctor->hospital_distance))
                                                {{ $doctor->hospital_distance }} km away
                                            @else
                                                View Map
                                            @endif
                                        </span>
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

                            <p class="card-text fw-bold mt-2"><i class="bi bi-heart-pulse text-danger"></i>⭐ {{ $doctor->doctorProfile->rating ?? '4.5' }} ({{ $doctor->doctorProfile->experience_years ?? 0 }} years exp.)</p>
                        </div>
                    </label>
                </div>
                @endforeach
                <div class="col-12 text-center py-4 d-none" id="no-doctors-msg">
                    <p class="text-muted mb-0">No doctors available in this hospital.</p>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('patient.book.create.step.one') }}" class="btn btn-secondary shadow-sm">← Back</a>
            <button type="submit" id="next-btn" class="btn btn-primary px-4 shadow-sm" disabled>Next →</button>
        </div>
    </form>
    
    <style>
        .card-radio input:checked + .card { border-color: #0066CC; box-shadow: 0 0 0 2px #0066CC; background-color: #f0f7ff; }
        .card-radio .card:hover { border-color: #a3c4f7; cursor: pointer; }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('doctor-selection-form');
            const nextButton = document.getElementById('next-btn');
            const hospitalSelect = document.getElementById('hospital-select');
            const doctorItems = document.querySelectorAll('.doctor-item');
            const noDoctorsMsg = document.getElementById('no-doctors-msg');

            if (navigator.geolocation && !window.location.search.includes('lat=')) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    window.location.href = window.location.pathname + '?lat=' + lat + '&lng=' + lng;
                }, function(error) {
                    console.log("Location access denied.");
                });
            }

            // --- Live Polling Logic ---
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

            // Handle Hospital Filter and Search
            const doctorSearch = document.getElementById('doctor-search');

            function filterDoctors() {
                const selectedHospital = hospitalSelect.value.toLowerCase();
                const searchQuery = doctorSearch.value.toLowerCase();
                let visibleCount = 0;
                
                doctorItems.forEach(item => {
                    const hospital = item.dataset.hospital.toLowerCase();
                    const name = item.querySelector('h5').textContent.toLowerCase();
                    const specialty = item.querySelector('.fw-bold[style*="color:#0066CC"]').textContent.toLowerCase();
                    
                    const matchesHospital = (selectedHospital === 'all' || hospital === selectedHospital);
                    const matchesSearch = (name.includes(searchQuery) || specialty.includes(searchQuery));

                    if (matchesHospital && matchesSearch) {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                if(visibleCount === 0) {
                    noDoctorsMsg.classList.remove('d-none');
                } else {
                    noDoctorsMsg.classList.add('d-none');
                }
            }

            hospitalSelect.addEventListener('change', () => {
                document.querySelectorAll('.doctor-radio').forEach(radio => radio.checked = false);
                nextButton.disabled = true;
                filterDoctors();
            });

            doctorSearch.addEventListener('input', filterDoctors);

            // Listen for changes on the radio buttons within the form
            form.addEventListener('change', function(event) {
                if (event.target.name === 'doctor_id') {
                    nextButton.disabled = false;
                }
            });
        });
    </script>
    @endpush
</x-layouts.dashboard>