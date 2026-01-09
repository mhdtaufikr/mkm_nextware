@php
    // load all of the current user’s positions
    $positions     = auth()->user()->positions;
    $userDepts     = $positions->pluck('department')->toArray();
    $userJobTitles = $positions->pluck('job_title')->toArray();

    $firstLog    = $detail->workflowLogs->first();
    // allow “Process” if this detail is assigned to one of the user’s departments and not yet filled
    $canProcess = $firstLog
               && in_array($firstLog->department_assigned, $userDepts)
               && $detail->fill != 1;

    // determine if user is Dept Head for THIS assigned department
    $isDeptHeadForThis = $positions->contains(function($pos) use ($firstLog) {
        return $pos->department === ($firstLog->department_assigned ?? null)
            && $pos->job_title  === 'Department Head';
    });

    // allow “Approve” if filled and user is either the creator or Dept Head of that department
    $canApprove = $detail->fill == 1
               && (
                    auth()->id() == $detail->auditor_id_no
                    || $isDeptHeadForThis
                  );

    // allow delete/edit if creator or the header-auditor
    $isHeaderAuditor = auth()->id() == $auditHeader->auditor;
    $canDelete       = auth()->id() == $detail->auditor_id_no
                    || $isHeaderAuditor;
    $canEdit         = $canDelete;
@endphp


<div class="dropdown">
  <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
          id="actionDropdown{{ $detail->id }}"
          data-bs-toggle="dropdown"
          aria-expanded="false">
    Actions
  </button>
  <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $detail->id }}">
    <li>
      <a class="dropdown-item"
         href="{{ route('detail.audit.view', Crypt::encryptString($detail->id)) }}">
        <i class="fas fa-eye me-2"></i>View
      </a>
    </li>
    <li>
        <a class="dropdown-item"
           href="{{ route('detail.audit.pdf', Crypt::encryptString($detail->id)) }}"
           target="_blank">
          <i class="fas fa-file-pdf me-2 text-danger"></i>Download PDF
        </a>
      </li>



    {{-- Process --}}
    <li>
      @if($canProcess)
        <a class="dropdown-item"
           href="{{ route('detail.audit.assign', Crypt::encryptString($detail->id)) }}">
          <i class="fas fa-tasks me-2"></i>Process
        </a>
      @else
        <span class="dropdown-item disabled">
          <i class="fas fa-tasks me-2"></i>Process
        </span>
      @endif
    </li>

    {{-- Approve --}}
    @if($canApprove)
      <li>
        <a class="dropdown-item"
           href="{{ route('detail.audit.check', Crypt::encryptString($detail->id)) }}">
          <i class="fas fa-user-check me-2"></i>Approve
        </a>
      </li>
    @endif

    {{-- Edit --}}
    @if($canEdit)
      <li>
        <a class="dropdown-item"
           href="{{ route('audit-details.edit', Crypt::encryptString($detail->id)) }}">
          <i class="fas fa-edit me-2"></i>Edit
        </a>
      </li>
    @endif

    {{-- Delete --}}
    @if($canDelete)
      <li>
        <form action="{{ route('audit-details.destroy', $detail->id) }}"
              method="POST"
              onsubmit="return confirm('Yakin ingin menghapus NC No. {{ $detail->nc_no }}?');">
          @csrf @method('DELETE')
          <button class="dropdown-item text-danger" type="submit">
            <i class="fas fa-trash-alt me-2"></i>Delete
          </button>
        </form>
      </li>
    @endif
  </ul>
</div>
