<x-layouts.dashboard>
    <div class="container py-4">
        <h2>Generate Bill</h2>

        <div class="card shadow mt-4">
            <div class="card-body">
                <h5>Prescription #{{ $prescription->id }}</h5>
                <p><strong>Patient:</strong> {{ $prescription->patient->name }}</p>
                <p><strong>Medicines:</strong></p>
                <ul>
                    @foreach(json_decode($prescription->medicines, true) ?? [] as $med)
                        <li>{{ $med }}</li>
                    @endforeach
                </ul>
                <hr>
                <form action="{{ route('pharmacist.prescriptions.bill.store', $prescription->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Consultation Fee (&#x20B9;)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#x20B9;</span>
                                <input type="number" step="0.01" name="consultation_fee" class="form-control" value="50.00" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Medicines Cost (&#x20B9;)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#x20B9;</span>
                                <input type="number" step="0.01" name="medicine_cost" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Submit Bill & Mark Completed</button>
                    <a href="{{ route('pharmacist.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>