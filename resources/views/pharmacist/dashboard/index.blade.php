<x-layouts.pharmacist>
    <div class="mb-4">
        <h2 class="h3 fw-bold mb-1">Lookup Patient Prescription</h2>
        <p class="text-muted">Verify tokens and generate digital medical bills.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif

    <!-- Search Medicine by Token & Doctor -->
    <div class="card shadow-sm border-0 mb-4 p-4" style="border-radius: 15px;">
        <form action="{{ route('pharmacist.dashboard') }}" method="GET">
            <div class="row align-items-end g-3">
                <div class="col-lg-4 col-sm-6">
                    <label class="form-label fw-bold small text-muted">Token Number</label>
                    <input type="number" name="token_number" value="{{ request('token_number') }}"
                        class="form-control form-control-lg bg-light" placeholder="Enter Patient Token" required>
                </div>
                <div class="col-lg-5 col-sm-6">
                    <label class="form-label fw-bold small text-muted">Select Doctor</label>
                    <select name="doctor_id" class="form-select form-select-lg bg-light" required>
                        <option value="">-- Select Doctor --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                Dr. {{ $doc->name }} ({{ $doc->doctorProfile->specialty ?? 'Specialist' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3">
                    <button type="submit" class="btn btn-lg btn-primary w-100 py-2"><i class="bi bi-search"></i> Find Prescription</button>
                </div>
            </div>
        </form>
    </div>

    @if(request('token_number') && request('doctor_id'))
        @if($prescription)
            <div class="card border-0 shadow-lg p-0 overflow-hidden" style="border-radius: 15px;">
                <div class="bg-success py-2 px-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold tracking-wider small text-uppercase">Prescription Verified</span>
                        <span class="small opacity-75">ID: #{{ $prescription->id }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-4 col-sm-6">
                            <p class="text-muted small mb-1">Patient Name</p>
                            <h5 class="fw-bold">{{ $prescription->patient->name }}</h5>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <p class="text-muted small mb-1">Prescribed By</p>
                            <h5 class="fw-bold">Dr. {{ $prescription->doctor->name }}</h5>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <p class="text-muted small mb-1">Current Status</p>
                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-3 fw-bold text-uppercase">{{ $prescription->status }}</span>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-capsule-pill me-2 text-primary"></i> Prescribed Medicines:
                    </h5>
                    <div class="row g-2 mb-4">
                        @forelse(json_decode($prescription->medicines, true) ?? [] as $med)
                            <div class="col-md-6 col-lg-4">
                                <div class="px-3 py-2 bg-light rounded text-dark fw-bold border">
                                    {{ $med }}
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><p class="text-muted italic">No medicines listed in record.</p></div>
                        @endforelse
                    </div>

                    @if($prescription->notes)
                        <div class="p-3 bg-warning bg-opacity-10 rounded mb-4 border border-warning border-opacity-25">
                            <p class="mb-0"><i class="bi bi-info-circle me-1"></i> <strong>Doctor Notes:</strong> {{ $prescription->notes }}</p>
                        </div>
                    @endif

                    <hr class="my-4">
                    <h5 class="fw-bold text-primary mb-3">Generate Digital Bill</h5>
                    <form action="{{ route('pharmacist.prescriptions.bill.store', $prescription->id) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-lg-5 col-sm-6">
                                <label class="fw-bold small text-muted mb-1">Consultation Fee (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="consultation_fee"
                                        class="form-control form-control-lg bg-light" value="500.00" required>
                                </div>
                            </div>
                            <div class="col-lg-5 col-sm-6">
                                <label class="fw-bold small text-muted mb-1">Medicine Cost (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="medicine_cost"
                                        class="form-control form-control-lg border-primary"
                                        placeholder="Enter total medicine cost" required autofocus>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <button class="btn btn-lg btn-success h-100 w-100 fw-bold shadow-sm py-2" type="submit">
                                    SAVE BILL
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 15px;">
                <i class="bi bi-exclamation-octagon text-danger display-4 mb-3"></i>
                <h4 class="fw-bold mb-2">No Prescription Found</h4>
                <p class="text-muted mb-0">We couldn't find a pending prescription for this token and doctor. <br> Please verify the details or check if the bill was already generated.</p>
            </div>
        @endif
    @else
        <div class="text-center text-muted my-5 py-5">
            <div class="bg-primary bg-opacity-10 d-inline-block p-4 rounded-circle mb-3">
                <i class="bi bi-search text-primary" style="font-size: 3rem;"></i>
            </div>
            <h5 class="fw-bold">Ready to process?</h5>
            <p>Enter the patient's token number and select the doctor to view the prescription.</p>
        </div>
    @endif
</x-layouts.pharmacist>