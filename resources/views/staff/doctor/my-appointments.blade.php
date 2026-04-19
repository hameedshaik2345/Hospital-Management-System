<x-layouts.doctor>
    <div class="mb-4">
        <h1 class="h2 fw-bold">My Appointments</h1>
        <p class="text-muted">View and manage your appointments.</p>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <form action="{{ route('doctor.appointments.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by patient name..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all" @selected(request('status') == 'all')>All Statuses</option>
                        <option value="scheduled" @selected(request('status') == 'scheduled')>Scheduled</option>
                        <option value="confirmed" @selected(request('status') == 'confirmed')>Confirmed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_range" class="form-label">Date Range</label>
                    <select name="date_range" id="date_range" class="form-select">
                        <option value="all" @selected(request('date_range') == 'all')>All Dates</option>
                        <option value="today" @selected(request('date_range') == 'today')>Today</option>
                        <option value="upcoming" @selected(request('date_range') == 'upcoming')>Upcoming</option>
                        <option value="past" @selected(request('date_range') == 'past')>Past</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-light w-50">Clear</a>
                </div></div>
            </div>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <!-- Appointments List -->
    <h3 class="h5 fw-bold mb-3">Appointments ({{ $appointments->count() }})</h3>
    <div class="d-flex flex-column gap-3">
        @forelse ($appointments as $appointment)
            <div class="card p-4">
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="fw-bold">{{ $appointment->patient->name ?? 'N/A' }}</h5>
                        <span class="badge rounded-pill
                            @switch($appointment->status)
                                @case('scheduled') bg-warning-subtle text-warning-emphasis @break
                                @case('confirmed') bg-info-subtle text-info-emphasis @break
                            @endswitch
                        ">{{ ucfirst($appointment->status) }}</span>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between">
                            <span>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</span>
                            <span>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}</span>
                            <span>{{ $appointment->doctor_specialty }}</span>
                        </div>
                        @if($appointment->reason)
                            <p class="mt-2 mb-0"><strong class="small">Reason:</strong> {{ $appointment->reason ?? 'Not given' }}</p>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal"
                            data-bs-target="#patientDetailModal"
                            data-patient-name="{{ $appointment->patient->name ?? 'N/A' }}"
                            data-patient-age="{{ $appointment->patient->age ?? 'N/A' }}"
                            data-patient-gender="{{ ucfirst($appointment->patient->gender ?? 'N/A') }}"
                            data-patient-phone="{{ $appointment->patient->phone_number ?? 'N/A' }}"
                            data-patient-email="{{ $appointment->patient->email ?? 'N/A' }}"
                            data-patient-address="{{ $appointment->patient->address ?? 'N/A' }}"
                            data-appointment-reason="{{ $appointment->reason ?? 'Not given' }}">
                        View Patient
                    </button>
                    @if ($appointment->status === 'scheduled')
                    <form action="{{ route('doctor.appointments.updateStatus', $appointment) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn btn-sm btn-info">Confirm</button>
                    </form>
                    @endif

                    <a href="{{ route('doctor.appointments.prescription.create', $appointment) }}" class="btn btn-sm btn-primary">Write Prescription</a>
                    <form action="{{ route('doctor.appointments.updateStatus', $appointment) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card p-5 text-center">
                <p class="text-muted mb-0">No active appointments match the current filters.</p>
            </div>
        @endforelse
    </div>

    <!-- NEW Colorful & Interactive Patient Details Modal -->
    <div class="modal fade" id="patientDetailModal" tabindex="-1" aria-labelledby="patientDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal-content">
          <div class="custom-modal-header">
            <div class="icon-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg>
            </div>
            <h4 id="modal-patient-name" class="mb-0"></h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <h6 class="mb-3">Reason for Visit</h6>
             <p id="modal-appointment-reason" class="text-muted border p-3 rounded"></p>
            <hr class="my-4">
            <h6 class="mb-3">Patient Information</h6>
            <div class="row g-4">
                <div class="col-6">
                    <div class="info-item">
                        <div class="info-label">Age</div>
                        <div id="modal-patient-age" class="info-value"></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div id="modal-patient-gender" class="info-value"></div>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <h6 class="mb-3"><strong style="color: black;">Contact Information :</strong></h6>
            <div class="d-flex flex-column gap-3">
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div id="modal-patient-phone" class="info-value"></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div id="modal-patient-email" class="info-value"></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div id="modal-patient-address" class="info-value"></div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Custom CSS for the new modal design -->
    <style>
        .custom-modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .custom-modal-header {
            background: linear-gradient(135deg, #0066CC, #00B4A6);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }
        .custom-modal-header .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .custom-modal-header .btn-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        .info-item .info-label {
            font-weight: bold;
            font-size: 0.8rem;
            color: #000000ff;
            margin-bottom: 0.25rem;
        }
        .info-item .info-value {
            font-weight: 500;
        }
        .modal.fade .modal-dialog {
            transform: scale(0.9);
            transition: transform 0.2s ease-out;
        }
        .modal.show .modal-dialog {
            transform: scale(1);
        }
    </style>

    @push('scripts')
    <script>
        const patientDetailModal = document.getElementById('patientDetailModal');
        if (patientDetailModal) {
            patientDetailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const patientName = button.getAttribute('data-patient-name');
                const patientAge = button.getAttribute('data-patient-age');
                const patientGender = button.getAttribute('data-patient-gender');
                const patientPhone = button.getAttribute('data-patient-phone');
                const patientEmail = button.getAttribute('data-patient-email');
                const patientAddress = button.getAttribute('data-patient-address');

                const modal = event.target;
                modal.querySelector('#modal-patient-name').textContent = patientName;
                modal.querySelector('#modal-patient-age').textContent = patientAge;
                modal.querySelector('#modal-patient-gender').textContent = patientGender;
                modal.querySelector('#modal-patient-phone').textContent = patientPhone;
                modal.querySelector('#modal-patient-email').textContent = patientEmail;
                modal.querySelector('#modal-patient-address').textContent = patientAddress;
                modal.querySelector('#modal-appointment-reason').textContent = button.getAttribute('data-appointment-reason');
            });
        }
    </script>
    @endpush
</x-layouts.doctor>