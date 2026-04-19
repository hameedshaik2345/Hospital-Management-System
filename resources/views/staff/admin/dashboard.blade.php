<x-layouts.admin>
    <div class="mb-4">
        <h1 class="h2 fw-bold">Admin Dashboard</h1>
        <p class="text-muted">High-level overview of the MedFlow system.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-2">
                            <i class="bi bi-people-fill text-primary"></i>
                        </div>
                        <h6 class="text-muted mb-0">Total Patients</h6>
                    </div>
                    <p class="fs-2 fw-bold mb-0 text-dark">{{ $totalPatients }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info bg-opacity-10 p-2 rounded-3 me-2">
                            <i class="bi bi-person-badge-fill text-info"></i>
                        </div>
                        <h6 class="text-muted mb-0">Total Doctors</h6>
                    </div>
                    <p class="fs-2 fw-bold mb-0 text-dark">{{ $totalDoctors }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 p-2 rounded-3 me-2">
                            <i class="bi bi-calendar-check-fill text-success"></i>
                        </div>
                        <h6 class="text-muted mb-0">Today's Apps</h6>
                    </div>
                    <p class="fs-2 fw-bold mb-0 text-dark">{{ $appointmentsToday }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3 me-2">
                            <i class="bi bi-hourglass-split text-warning"></i>
                        </div>
                        <h6 class="text-muted mb-0">Pending</h6>
                    </div>
                    <p class="fs-2 fw-bold mb-0 text-dark">{{ $pendingAppointments }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 mt-2">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="card-body d-flex justify-content-between align-items-center py-4">
                    <div>
                        <h5 class="opacity-75 mb-1">Total Hospital Revenue</h5>
                        <p class="display-5 fw-bold mb-0">₹{{ number_format($totalRevenue, 2) }}</p>
                    </div>
                    <i class="bi bi-currency-rupee display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <h3 class="h4 fw-bold mb-3">Recent Registrations</h3>
            <div class="card">
                <ul class="list-group list-group-flush">
                    @forelse ($recentUsers as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <p class="fw-bold mb-0">{{ $user->name }}</p>
                                <p class="small text-muted mb-0">{{ $user->email }}</p>
                            </div>
                            <span class="badge rounded-pill 
                                        @if($user->role == 'patient') bg-primary-subtle text-primary-emphasis
                                        @else bg-info-subtle text-info-emphasis @endif
                                    ">{{ ucfirst($user->role) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No recent registrations.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <h3 class="h4 fw-bold mb-3">Upcoming Appointments</h3>
            <div class="card">
                <ul class="list-group list-group-flush">
                    @forelse ($upcomingAppointments as $appointment)
                        <li class="list-group-item">
                            <p class="fw-bold mb-0">{{ $appointment->patient->name ?? 'N/A' }} with
                                {{ $appointment->doctor->name ?? 'N/A' }}
                            </p>
                            <p class="small text-muted mb-0">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y \a\t h:i A') }}
                            </p>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No upcoming appointments.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-md-12">
            <h3 class="h4 fw-bold mb-3">Doctor-wise Appointment Stats</h3>
            <div class="card p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Doctor</th>
                                <th class="pe-4 text-end">Total Appointments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctorStats as $stat)
                                <tr>
                                    <td class="ps-4">{{ $stat->doctor->name ?? 'Unknown' }}</td>
                                    <td class="pe-4 text-end">{{ $stat->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4">No stats available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>