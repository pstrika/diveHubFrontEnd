{{-- One "add here" slot on the customise page (Zach, 2026-11-10; headings
     2026-11-11). Closed it is a thin line with a plus; open it is a box to
     type a step or a heading into. $after names the row directly above
     (a step's key, or h:<id> for a heading), '' for the very top. --}}
<details class="dh-ccr-add-slot" @if ($open) open @endif>
    <summary><span class="material-icons-round" aria-hidden="true">add</span>Add here</summary>
    <form method="POST" action="{{ route('CcrChecklist.custom.store', $stage) }}" class="dh-ccr-step-form">
        @csrf
        <input type="hidden" name="unit" value="{{ $unit->uuid }}">
        <input type="hidden" name="after" value="{{ $after }}">
        <div class="dh-ccr-kind" role="radiogroup" aria-label="What to add">
            <label><input type="radio" name="kind" value="step" checked><span>A step</span></label>
            <label><input type="radio" name="kind" value="heading"><span>A heading</span></label>
        </div>
        <textarea class="form-control border" name="text" rows="2" maxlength="500" required placeholder="Write it the way you want to read it on the boat" aria-label="Text of the new step or heading"></textarea>
        <button type="submit" class="btn btn-info btn-sm mb-0">Add</button>
    </form>
</details>
