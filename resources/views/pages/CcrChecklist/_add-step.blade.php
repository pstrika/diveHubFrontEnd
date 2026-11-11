{{-- One "add a step here" slot on the customise page (Zach, 2026-11-10).
     Closed it is a thin line with a plus; open it is a box to type the step
     into. $after is the key of the row directly above, '' for the top. --}}
<details class="dh-ccr-add-slot" @if ($open) open @endif>
    <summary><span class="material-icons-round" aria-hidden="true">add</span>Add a step here</summary>
    <form method="POST" action="{{ route('CcrChecklist.custom.store', $stage) }}" class="dh-ccr-step-form">
        @csrf
        <input type="hidden" name="unit" value="{{ $unit->uuid }}">
        <input type="hidden" name="after" value="{{ $after }}">
        <textarea class="form-control border" name="text" rows="2" maxlength="500" required placeholder="Write the step the way you want to read it on the boat" aria-label="New step"></textarea>
        <button type="submit" class="btn btn-info btn-sm mb-0">Add step</button>
    </form>
</details>
