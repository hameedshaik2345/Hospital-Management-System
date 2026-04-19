<x-layouts.dashboard>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Complete Payment</h4>
                    </div>
                    <div class="card-body">
                        <h5>Appointment Summary</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @php
                                    $payDocName = $appointment->doctor_name;
                                    if (!str_starts_with(strtolower($payDocName), 'dr.')) {
                                        $payDocName = 'Dr. ' . $payDocName;
                                    }
                                @endphp
                                <span>{{ $payDocName }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Specialty
                                <span>{{ $appointment->doctor_specialty }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Date & Time
                                <span>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y h:i A') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Token Number
                                <span class="badge bg-primary rounded-pill">#{{ $appointment->token_number }}</span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
                                Total Fee
                                <span>₹200.00</span>
                            </li>
                        </ul>

                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><i class="bi bi-qr-code-scan"></i> How to pay with QR Code:</h6>
                                <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-bug-fill"></i> Test Mode</span>
                            </div>
                            <ol class="small mb-0 ps-3">
                                <li>Click the **"Pay & Confirm"** button below.</li>
                                <li>In the secure window, select **UPI** or **QR Code**.</li>
                                <li><strong>To simulate success:</strong> Click the "Success" button in the test window or use UPI ID: <code>success@razorpay</code></li>
                            </ol>
                        </div>

                        <div id="payment-actions">
                            <button type="button" id="pay-btn" class="btn btn-success w-100 btn-lg fw-bold shadow-sm py-3">
                                <i class="bi bi-shield-check"></i> Pay ₹200 & Confirm Booking
                            </button>
                            
                            <form action="{{ route('patient.appointments.destroy', $appointment->id) }}" method="POST"
                                class="mt-3 text-center">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link text-muted text-decoration-none small">
                                    <i class="bi bi-x-circle"></i> Cancel & Release Token
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const payBtn = document.getElementById('pay-btn');
        
        payBtn.addEventListener('click', async function() {
            payBtn.disabled = true;
            payBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Initializing Secure Payment...';

            try {
                // 1. Create Order
                const response = await fetch("{{ route('patient.book.pay', $appointment->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const orderData = await response.json();

                if (orderData.is_demo) {
                    // SHOW BEAUTIFUL FAILOVER SIMULATION MODAL
                    const simulationHTML = `
                        <div id="sim-backdrop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px);">
                            <div style="background:white;width:420px;border-radius:24px;overflow:hidden;box-shadow:0 30px 60px -12px rgba(0,0,0,0.5);animation:slideUp 0.4s cubic-bezier(0, 0, 0.2, 1);">
                                <div style="background:linear-gradient(135deg, #0066CC, #00B4A6);padding:30px;text-align:center;color:white;">
                                    <div class="mb-2"><i class="bi bi-shield-check fs-1"></i></div>
                                    <h4 class="mb-0 fw-bold">MedFlow Pay Simulator</h4>
                                    <div class="small opacity-75 mt-1">Safe Demo Environment</div>
                                </div>
                                <div class="p-4 text-center">
                                    <div class="mb-4">
                                        <div class="text-muted small fw-bold mb-3 tracking-widest text-uppercase">Scan QR to Simulate Payment</div>
                                        <div class="bg-white p-3 d-inline-block rounded-4 shadow-sm border">
                                            <i class="bi bi-qr-code" style="font-size: 160px; color: #1a1a1a;"></i>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <span class="text-muted small">Total Fee:</span>
                                        <h2 class="fw-bold text-dark mb-0">₹200.00</h2>
                                    </div>
                                    <button id="confirm-sim" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-pill shadow-lg mb-3 border-0" style="background: linear-gradient(135deg, #0066CC, #00B4A6);">
                                        Confirm Payment (Success)
                                    </button>
                                    <button id="cancel-sim" class="btn btn-link text-muted btn-sm text-decoration-none">Cancel Simulation</button>
                                </div>
                                <div class="bg-light p-3 text-center small text-muted border-top">
                                    <i class="bi bi-lock-fill me-1"></i> Developer Demo Mode Active
                                </div>
                            </div>
                        </div>
                        <style>@keyframes slideUp { from { transform:translateY(80px); opacity:0; } to { transform:translateY(0); opacity:1; } }</style>
                    `;
                    document.body.insertAdjacentHTML('beforeend', simulationHTML);

                    document.getElementById('confirm-sim').onclick = function() {
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finalizing Booking...';
                        this.disabled = true;
                        setTimeout(() => {
                            window.location.href = "{{ route('patient.book.confirmation') }}";
                        }, 1800);
                    };

                    document.getElementById('cancel-sim').onclick = function() {
                        document.getElementById('sim-backdrop').remove();
                        payBtn.disabled = false;
                        payBtn.innerHTML = '<i class="bi bi-shield-check"></i> Pay ₹200 & Confirm Booking';
                    };
                    return;
                }

                if (orderData.error) {
                    alert(orderData.error);
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="bi bi-shield-check"></i> Pay ₹200 & Confirm Booking';
                    return;
                }

                // 2. Open Razorpay
                const options = {
                    "key": orderData.key,
                    "amount": orderData.amount,
                    "currency": "INR",
                    "name": orderData.name,
                    "description": orderData.description,
                    "order_id": orderData.order_id,
                    "handler": async function (response){
                        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Finalizing Booking...';
                        const verifyRes = await fetch("{{ route('patient.book.verify', $appointment->id) }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature
                            })
                        });

                        const verifyData = await verifyRes.json();
                        if (verifyData.success) {
                            window.location.href = "{{ route('patient.book.confirmation') }}";
                        } else {
                            alert("Booking verification failed. Please check your dashboard.");
                            window.location.href = "{{ route('patient.dashboard') }}";
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
                            payBtn.disabled = false;
                            payBtn.innerHTML = '<i class="bi bi-shield-check"></i> Pay ₹200 & Confirm Booking';
                        }
                    }
                };
                const rzp = new Razorpay(options);
                rzp.open();

            } catch (error) {
                console.error("Payment Error:", error);
                alert("Simulation Failover Initialized.");
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="bi bi-shield-check"></i> Pay ₹200 & Confirm Booking';
            }
        });
    </script>
    @endpush
</x-layouts.dashboard>