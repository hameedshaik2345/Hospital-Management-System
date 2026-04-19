<x-layouts.dashboard>
    <!-- Dashboard Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0 text-dark">Dashboard</h2>
        <a href="{{ route('patient.symptoms.index') }}" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm" style="border-radius: 8px;">
            <i class="bi bi-robot"></i> 
            <span>Symptom Analyzer</span>
        </a>
    </div>

    @foreach($upcomingAppointments as $appointment)
        @php
            $tokensLeft = ($appointment->token_number ?? 0) - ($appointment->current_token ?? 0);
            $docName = $appointment->doctor_name;
            if (!str_starts_with(strtolower($docName), 'dr.')) {
                $docName = 'Dr. ' . $docName;
            }
        @endphp

        @if($tokensLeft >= -5 && $tokensLeft <= 10)
            @if($tokensLeft > 0)
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" role="alert"
                    style="background: white; border-radius: 12px; border-left: 5px solid #ffc107 !important;">
                    <div class="me-3 fs-4 text-warning">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark">Your appointment is approaching!</strong><br>
                                <span class="text-muted small">{{ $docName }} (Token #{{ $appointment->token_number }})</span>
                            </div>
                            <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                                {{ $tokensLeft }} patients to go
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif($tokensLeft < 0)
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 d-flex align-items-center" role="alert"
                    style="background: white; border-radius: 12px; border-left: 5px solid #dc3545 !important;">
                    <div class="me-3 fs-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="text-dark">Appointment Time Expired</strong><br>
                        <span class="text-muted small">Your token #{{ $appointment->token_number }} with <strong>{{ $docName }}</strong> has already passed.</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        @endif
    @endforeach

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold mb-3">Total Appointments</h6>
                    <p class="fs-2 fw-bold mb-0 text-dark">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold mb-3">Upcoming</h6>
                    <p class="fs-2 fw-bold mb-0 text-warning">{{ $stats['upcoming'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold mb-3">Completed</h6>
                    <p class="fs-2 fw-bold mb-0 text-success">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="text-muted small fw-bold mb-3">Cancelled</h6>
                    <p class="fs-2 fw-bold mb-0 text-danger">{{ $stats['cancelled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <h3 class="h5 fw-bold mb-4 text-dark">Upcoming Appointments</h3>

    <div class="space-y-4">
        @forelse($upcomingAppointments as $appointment)
            @php
                $apptDocName = $appointment->doctor_name;
                if (!str_starts_with(strtolower($apptDocName), 'dr.')) {
                    $apptDocName = 'Dr. ' . $apptDocName;
                }
            @endphp
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $apptDocName }}</h6>
                            <p class="text-muted small mb-2">{{ $appointment->doctor_specialty }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 text-capitalize">{{ $appointment->status }}</span>
                                @if($appointment->token_number)
                                    <span class="badge bg-success-subtle text-success-emphasis px-2 py-1">Token: {{ $appointment->token_number }}</span>
                                @endif
                                <span class="text-muted small d-flex align-items-center ms-2">
                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d \a\t h:i A') }}
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary px-4 py-2 fw-semibold" style="border-radius: 8px;"
                            data-bs-toggle="modal"
                            data-bs-target="#appointmentDetailModal" data-status="{{ ucfirst($appointment->status) }}"
                            data-date="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }}"
                            data-time="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}"
                            data-doctor="{{ $apptDocName }}" data-specialty="{{ $appointment->doctor_specialty }}"
                            data-token="{{ $appointment->token_number ?? 'N/A' }}"
                            data-current-token="{{ $appointment->current_token ?? 0 }}"
                            data-live-status="{{ $appointment->live_status ?? 'Unavailable' }}"
                            data-reason="{{ $appointment->reason ?? 'Not given' }}">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 12px;">
                <p class="text-muted mb-0">No upcoming appointments found.</p>
                <a href="{{ route('patient.book.create.step.one') }}" class="btn btn-link">Book Your First Appointment</a>
            </div>
        @endforelse
    </div>

    <style>
        .space-y-4 > * + * { margin-top: 1rem; }
    </style>

    <!-- REDESIGNED Appointment Details Modal -->
    <div class="modal fade" id="appointmentDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content custom-modal-content">
                <div class="custom-modal-header">
                    <div class="icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                            class="bi bi-calendar2-check" viewBox="0 0 16 16">
                            <path
                                d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
                            <path
                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z" />
                            <path
                                d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z" />
                        </svg>
                    </div>
                    <h4 class="modal-title">Appointment Details</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="appointmentDetailsBody">
                    <!-- Content will be injected by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
            overflow: hidden
        }

        .custom-modal-header {
            background: linear-gradient(135deg, #0066CC, #00B4A6);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative
        }

        .custom-modal-header .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem
        }

        .custom-modal-header .btn-close {
            position: absolute;
            top: 1rem;
            right: 1rem
        }
    </style>

    @push('scripts')
        <script>
            const appointmentDetailModal = document.getElementById('appointmentDetailModal');
            if (appointmentDetailModal) {
                appointmentDetailModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const modalBody = document.getElementById('appointmentDetailsBody');

                    // Extract all data from the button
                    const status = button.getAttribute('data-status');
                    const date = button.getAttribute('data-date');
                    const time = button.getAttribute('data-time');
                    const doctor = button.getAttribute('data-doctor');
                    const specialty = button.getAttribute('data-specialty');
                    const reason = button.getAttribute('data-reason');
                    const token = button.getAttribute('data-token');
                    const currentToken = parseInt(button.getAttribute('data-current-token')) || 0;
                    const liveStatus = button.getAttribute('data-live-status') || 'Unavailable';

                    // Build the new, beautiful modal content
                    modalBody.innerHTML = `
                        <div class="p-2">
                            <div class="text-center mb-5 d-none d-lg-block">
                                <a href="{{ route('patient.dashboard') }}">
                                    <img src="{{ asset('medflow-logo.png') }}" alt="MedFlow" 
                                         style="height: 70px; max-width: 100%; object-fit: contain;">
                                </a>
                            </div>
                            <div class="row text-center mb-3">
                                <div class="col"><span class="badge fs-6 text-capitalize bg-warning-subtle text-warning-emphasis">${status}</span></div>
                                ${token != 'N/A' && token != null && token != '' ? `<div class="col"><span class="badge fs-6 bg-success text-white">🎟️ Your Token: ${token}</span></div>` : ''}
                            </div>

                            <!-- Live Token Status Box -->
                            <div class="rounded-3 p-3 mb-4 text-center" style="background: linear-gradient(135deg, #e0f2fe, #f0f9ff); border: 1px solid #b0d4f0;">
                                <p class="text-muted small mb-1 fw-semibold">🏥 LIVE TOKEN STATUS</p>
                                <div class="d-flex justify-content-center gap-4 align-items-center">
                                    <div>
                                        <p class="mb-0 text-muted small">Currently Serving</p>
                                        <span class="fw-bold fs-3 text-primary">#${currentToken}</span>
                                    </div>
                                    <div class="fs-4">→</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Your Token</p>
                                        <span class="fw-bold fs-3 text-success">#${token}</span>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-muted small">Ahead of You</p>
                                        <span class="fw-bold fs-3 text-warning">${Math.max(0, token - currentToken)}</span>
                                    </div>
                                </div>
                                <p class="mb-0 mt-2 small text-muted">Doctor Status: <strong>${liveStatus}</strong></p>
                            </div>

                            <h6 class="mt-2">Doctor Information</h6>
                            <div class="row">
                                <div class="col-md-6"><p><small class="text-muted">Name</small><br>${doctor}</p></div>
                                <div class="col-md-6"><p><small class="text-muted">Specialty</small><br>${specialty}</p></div>
                            </div>
                            <hr>
                            <h6 class="mt-4">Appointment Details</h6>
                            <div class="row">
                                <div class="col-md-6"><p><small class="text-muted">Date</small><br>${date}</p></div>
                                <div class="col-md-6"><p><small class="text-muted">Time</small><br>${time}</p></div>
                                <div class="col-12"><p><small class="text-muted">Reason for Visit</small><br>${reason}</p></div>
                            </div>
                        </div>
                    `;
                });
            }
        </script>
        
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.querySelectorAll('.pay-now-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const prescriptionId = this.dataset.prescriptionId;
                    const url = this.dataset.url;
                    const btn = this;
                    
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const orderData = await response.json();

                        if (orderData.error) {
                            alert(orderData.error);
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-credit-card"></i> Pay Now';
                            return;
                        }

                        const options = {
                            "key": orderData.key,
                            "amount": orderData.amount,
                            "currency": "INR",
                            "name": orderData.name,
                            "description": orderData.description,
                            "order_id": orderData.order_id,
                            "handler": async function (response){
                                const verifyUrl = `{{ url('patient/prescriptions') }}/${prescriptionId}/verify-payment`;
                                const verifyRes = await fetch(verifyUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        razorpay_payment_id: response.razorpay_payment_id,
                                        razorpay_order_id: response.razorpay_order_id,
                                        razorpay_signature: response.razorpay_signature
                                    })
                                });

                                const verifyData = await verifyRes.json();
                                if (verifyData.success) {
                                    window.location.reload();
                                } else {
                                    alert("Payment verification failed. Please contact support.");
                                    btn.disabled = false;
                                    btn.innerHTML = '<i class="bi bi-credit-card"></i> Pay Now';
                                }
                            },
                            "prefill": {
                                "name": orderData.user_name,
                                "email": orderData.user_email,
                                "contact": orderData.user_phone
                            },
                            "theme": { "color": "#0066CC" },
                            "modal": {
                                "ondismiss": function(){
                                    btn.disabled = false;
                                    btn.innerHTML = '<i class="bi bi-credit-card"></i> Pay Now';
                                }
                            }
                        };
                        const rzp = new Razorpay(options);
                        rzp.open();

                    } catch (error) {
                        console.error("Payment Error:", error);
                        alert("Something went wrong. Please try again.");
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-credit-card"></i> Pay Now';
                    }
                });
            });
        </script>
    @endpush
</x-layouts.dashboard>