<x-layouts.doctor>
    <div class="mb-4">
        <h1 class="h2 fw-bold">Welcome, {{ $doctor->name }}!</h1>
        <p class="text-muted">Manage your appointments and patient care.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="row g-4 mb-4">
                <div class="col-md-6"><div class="card h-100"><h5>Total Appointments</h5><p class="fs-2 fw-bold mb-0">{{ $stats['total'] }}</p></div></div>
                <div class="col-md-6"><div class="card h-100"><h5>Upcoming</h5><p class="fs-2 fw-bold text-warning mb-0">{{ $stats['upcoming'] }}</p></div></div>
                <div class="col-md-6"><div class="card h-100"><h5>Completed</h5><p class="fs-2 fw-bold text-success mb-0">{{ $stats['completed'] }}</p></div></div>
                <div class="col-md-6"><div class="card h-100"><h5>Patients Today</h5><p class="fs-2 fw-bold text-info mb-0">{{ $stats['patients_today'] }}</p></div></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #e0f2fe 0%, #f8fafc 100%);">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-primary d-flex align-items-center">
                        <span class="bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                            <i class="bi bi-broadcast"></i>
                        </span>
                        Live Control
                    </h4>
                    
                    <div class="text-center mb-5 p-3 bg-white rounded-4 shadow-sm">
                        <p class="text-muted small mb-1 fw-semibold text-uppercase tracking-wider">Currently Serving</p>
                        <div class="display-2 fw-bold text-dark" id="live-token-display">
                            #{{ $doctor->doctorProfile->current_token ?? 0 }}
                        </div>
                        <button class="btn btn-primary btn-lg w-100 rounded-pill mt-3 py-3 shadow fw-bold" id="next-token-btn">
                            <i class="bi bi-person-plus-fill me-2"></i> NEXT PATIENT
                        </button>
                    </div>

                    <div class="bg-white p-3 rounded-4 shadow-sm">
                        <label class="fw-bold mb-3 small text-muted text-uppercase">Availability Status</label>
                        <select class="form-select form-select-lg border-0 bg-light cursor-pointer" id="live-status-select">
                            <option value="Available" {{ ($doctor->doctorProfile->live_status ?? '') == 'Available' ? 'selected' : '' }}>🟢 Available</option>
                            <option value="Lunch Break" {{ ($doctor->doctorProfile->live_status ?? '') == 'Lunch Break' ? 'selected' : '' }}>🍔 Lunch Break</option>
                            <option value="In Surgery" {{ ($doctor->doctorProfile->live_status ?? '') == 'In Surgery' ? 'selected' : '' }}>✂️ In Surgery</option>
                            <option value="Rounding Wards" {{ ($doctor->doctorProfile->live_status ?? '') == 'Rounding Wards' ? 'selected' : '' }}>🏥 Ward Rounds</option>
                            <option value="Out of Office" {{ ($doctor->doctorProfile->live_status ?? '') == 'Out of Office' ? 'selected' : '' }}>🔴 Out of Office</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3 class="h4 fw-bold mb-3">Check Daily Schedule</h3>
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border-bottom">
            <button type="button" id="prev-month" class="btn btn-light">‹</button>
            <h5 id="current-month-year" class="fw-bold mb-0"></h5>
            <button type="button" id="next-month" class="btn btn-light">›</button>
        </div>
        <div id="calendar" class="calendar-grid p-3">
            </div>
    </div>

    <div id="selected-day-container" class="mb-4" style="display: none;">
        <h3 class="h4 fw-bold mb-3" id="schedule-title"></h3>
        <div class="card" id="schedule-list-container">
            </div>
    </div>


    <div>
      <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="h4 fw-bold mb-0">Today's Schedule</h3>
    <a href="{{ route('doctor.schedule.export.doctor') }}" class="btn btn-sm btn-outline-primary">
        Export Today's PDF
    </a>
</div>

        <p class="text-muted">{{ \Carbon\Carbon::today()->format('l, F j, Y') }}</p>
        <div class="card">
            @forelse ($todaysSchedule as $appointment)
                <div class="d-flex justify-content-between align-items-center p-3 @if(!$loop->last) border-bottom @endif">
                    <div>
                        <h6 class="fw-bold mb-1">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}</h6>
                        <p class="mb-0">Patient: {{ $appointment->patient->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary view-details-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#patientDetailModal"
                                data-patient-name="{{ $appointment->patient->name ?? 'N/A' }}"
                                data-patient-age="{{ $appointment->patient->age ?? 'N/A' }}"
                                data-patient-gender="{{ ucfirst($appointment->patient->gender ?? 'N/A') }}"
                                data-patient-phone="{{ $appointment->patient->phone_number ?? 'N/A' }}"
                                data-patient-email="{{ $appointment->patient->email ?? 'N/A' }}"
                                data-patient-address="{{ $appointment->patient->address ?? 'N/A' }}"
                                data-appointment-reason="{{ $appointment->reason ?? 'Not given' }}">
                            View Details
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center p-5">
                    <p class="text-muted">No appointments scheduled for today.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="modal fade" id="patientDetailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal-content">
          <div class="custom-modal-header">
            <div class="icon-box"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg></div>
            <h4 id="modal-patient-name" class="mb-0"></h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <h6 class="mb-3">Reason for Visit</h6>
            <p id="modal-appointment-reason-dash" class="text-muted border p-3 rounded"></p>
            <hr class="my-4">
            <h6 class="mb-3">Patient Information</h6>
            <div class="row g-4">
                <div class="col-6"><div class="info-item"><div class="info-label">Age</div><div id="modal-patient-age" class="info-value"></div></div></div>
                <div class="col-6"><div class="info-item"><div class="info-label">Gender</div><div id="modal-patient-gender" class="info-value"></div></div></div>
            </div>
            <hr class="my-4"><h6 class="mb-3">Contact Information</h6>
            <div class="d-flex flex-column gap-3">
                <div class="info-item"><div class="info-label">Phone</div><div id="modal-patient-phone" class="info-value"></div></div>
                <div class="info-item"><div class="info-label">Email</div><div id="modal-patient-email" class="info-value"></div></div>
                <div class="info-item"><div class="info-label">Address</div><div id="modal-patient-address" class="info-value"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
        .custom-modal-content { border-radius: 1rem; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .custom-modal-header { background: linear-gradient(135deg, #0066CC, #00B4A6); color: white; padding: 1.5rem; text-align: center; position: relative; }
        .custom-modal-header .icon-box { width: 60px; height: 60px; border-radius: 50%; background-color: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .custom-modal-header .btn-close { position: absolute; top: 1rem; right: 1rem; }
        .info-item .info-label { font-size: 0.8rem; color: #6c757d; margin-bottom: 0.25rem; }
        .info-item .info-value { font-weight: 500; }
        .modal.fade .modal-dialog { transform: scale(0.9); transition: transform 0.2s ease-out; }
        .modal.show .modal-dialog { transform: scale(1); }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; text-align: center; }
        .calendar-day { padding: 0.5rem; border-radius: 5%; cursor: pointer; }
        .calendar-day.disabled { color: #adb5bd; cursor: not-allowed; }
        .calendar-day:not(.disabled):not(.empty):hover { background-color: #99b1ffff; }
        .calendar-day.selected { background-color: #2563eb; color: white; font-weight: bold; }
        .calendar-day.today { border: 1px solid #2563eb; }
    </style>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Modal Script ---
        const patientDetailModal = document.getElementById('patientDetailModal');
        if (patientDetailModal) {
            patientDetailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const modal = event.target;
                modal.querySelector('#modal-patient-name').textContent = button.getAttribute('data-patient-name');
                modal.querySelector('#modal-patient-age').textContent = button.getAttribute('data-patient-age');
                modal.querySelector('#modal-patient-gender').textContent = button.getAttribute('data-patient-gender');
                modal.querySelector('#modal-patient-phone').textContent = button.getAttribute('data-patient-phone');
                modal.querySelector('#modal-patient-email').textContent = button.getAttribute('data-patient-email');
                modal.querySelector('#modal-patient-address').textContent = button.getAttribute('data-patient-address');
                 modal.querySelector('#modal-appointment-reason-dash').textContent = button.getAttribute('data-appointment-reason');
            });
        }
        
        // --- Calendar and Dynamic Schedule Script ---
        const calendarEl = document.getElementById('calendar');
        const monthYearEl = document.getElementById('current-month-year');
        const prevBtn = document.getElementById('prev-month');
        const nextBtn = document.getElementById('next-month');
        const selectedDayContainer = document.getElementById('selected-day-container');
        const scheduleTitle = document.getElementById('schedule-title');
        const scheduleContainer = document.getElementById('schedule-list-container');
        
        // --- Live Tracker Script ---
        const nextTokenBtn = document.getElementById('next-token-btn');
        const statusSelect = document.getElementById('live-status-select');
        const tokenDisplay = document.getElementById('live-token-display');

        if(nextTokenBtn) {
            nextTokenBtn.addEventListener('click', async () => {
                try {
                    const res = await fetch("{{ route('doctor.api.live_status.increment') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if(data.success) {
                        tokenDisplay.textContent = '#' + data.current_token;
                    }
                } catch(e) { console.error('Error incrementing token', e); }
            });
        }

        if(statusSelect) {
            statusSelect.addEventListener('change', async (e) => {
                const newStatus = e.target.value;
                try {
                    await fetch("{{ route('doctor.api.live_status.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                } catch(e) { console.error('Error updating status', e); }
            });
        }

        
        let currentDate = new Date();
        currentDate.setDate(1);

        function renderCalendar() {
            calendarEl.innerHTML = `<div class="fw-bold">Sun</div><div class="fw-bold">Mon</div><div class="fw-bold">Tue</div><div class="fw-bold">Wed</div><div class="fw-bold">Thu</div><div class="fw-bold">Fri</div><div class="fw-bold">Sat</div>`;
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            monthYearEl.textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;
            
            const firstDayOfMonth = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDayOfMonth; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.classList.add('calendar-day', 'empty');
                calendarEl.appendChild(emptyDiv);
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set to start of today for accurate comparison

            for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement('div');
                dayEl.classList.add('calendar-day');
                dayEl.textContent = day;
                
                const loopDate = new Date(year, month, day);
                
                // *** THIS IS THE CORRECTED LOGIC ***
                if (loopDate < today) {
                    dayEl.classList.add('disabled');
                } else {
                    if (loopDate.toDateString() === today.toDateString()) {
                        dayEl.classList.add('today', 'selected');
                    }
                    dayEl.addEventListener('click', () => {
                        document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
                        dayEl.classList.add('selected');
                        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        loadAppointmentsForDate(dateStr);
                    });
                }
                calendarEl.appendChild(dayEl);
            }
        }

        async function loadAppointmentsForDate(dateStr) {
            selectedDayContainer.style.display = 'block';
            scheduleTitle.textContent = `Schedule for ${dateStr}`;
            scheduleContainer.innerHTML = '<div class="p-5 text-center text-muted">Loading...</div>';
            
            try {
                const response = await fetch(`{{ route('doctor.api.appointments.by_date') }}?date=${dateStr}`);
                if (!response.ok) throw new Error('Network response was not ok');
                const appointments = await response.json();
                
                scheduleContainer.innerHTML = '';
                if (appointments.length > 0) {
                    appointments.forEach(app => {
                        const appDate = new Date(app.appointment_date);
                        const time = appDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

                        const appElement = document.createElement('div');
                        appElement.className = 'd-flex justify-content-between align-items-center p-3 border-bottom';
                        appElement.innerHTML = `
                            <div>
                                <h6 class="fw-bold mb-1">${time}</h6>
                                <p class="mb-0">Patient: ${app.patient ? app.patient.name : 'N/A'}</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary view-details-btn" 
                                        data-bs-toggle="modal" data-bs-target="#patientDetailModal"
                                        data-patient-name="${app.patient ? app.patient.name : ''}" 
                                        data-patient-age="${app.patient ? app.patient.age : ''}" 
                                        data-patient-gender="${app.patient ? app.patient.gender : ''}"
                                        data-patient-phone="${app.patient ? app.patient.phone_number : ''}"
                                        data-patient-email="${app.patient ? app.patient.email : ''}"
                                        data-patient-address="${app.patient ? app.patient.address : ''}">
                                    View Details
                                </button>
                            </div>
                        `;
                        scheduleContainer.appendChild(appElement);
                    });
                } else {
                    scheduleContainer.innerHTML = '<div class="p-5 text-center text-muted">No appointments scheduled for this day.</div>';
                }
            } catch (error) {
                console.error('Error fetching appointments:', error);
                scheduleContainer.innerHTML = '<div class="p-5 text-center text-danger">Could not load schedule.</div>';
            }
        }

        prevBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
        nextBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

        // Initial Load for today's date
        const todayStr = new Date().toISOString().slice(0, 10);
        renderCalendar();
        loadAppointmentsForDate(todayStr); // Load today's schedule into the dynamic section by default
        document.querySelector('#selected-day-container').style.display = 'none'; // Keep it hidden on initial load

    });
    </script>
    @endpush
</x-layouts.doctor>