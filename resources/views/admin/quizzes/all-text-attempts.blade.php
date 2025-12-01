@extends('layouts.admin')

@section('title', 'All Text Attempts')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All Text Question Attempts</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.manual-grading') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Manual Grading
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($attempts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Quiz</th>
                                        <th>Question</th>
                                        <th>Answer</th>
                                        <th>Status</th>
                                        <th>Points</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($attempt->user->profile_picture)
                                                        <img src="{{ Storage::url($attempt->user->profile_picture) }}"
                                                             alt="{{ $attempt->user->name }}"
                                                             class="rounded-circle me-2"
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                                             style="width: 32px; height: 32px;">
                                                            <span class="text-white fw-bold">{{ substr($attempt->user->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $attempt->user->name }}</div>
                                                        <small class="text-muted">{{ $attempt->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $attempt->quiz->title }}</span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $attempt->question->question_text }}">
                                                    {{ $attempt->question->question_text }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;" title="{{ $attempt->user_answer }}">
                                                    {{ $attempt->user_answer }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($attempt->graded_at)
                                                    <span class="badge bg-success">Graded</span>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($attempt->graded_at)->format('M d, Y H:i') }}
                                                    </small>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $attempt->points_earned > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $attempt->points_earned }}/{{ $attempt->question->points }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $attempt->created_at->format('M d, Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.quiz-attempts.details', $attempt->id) }}"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!$attempt->graded_at)
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-success"
                                                                onclick="gradeAttempt({{ $attempt->id }})"
                                                                title="Grade Attempt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $attempts->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Text Attempts Found</h5>
                            <p class="text-muted">There are no text question attempts in the system.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grading Modal -->
<div class="modal fade" id="gradingModal" tabindex="-1" aria-labelledby="gradingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradingModalLabel">Grade Text Answer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="gradingForm">
                <div class="modal-body">
                    <div id="gradingContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function gradeAttempt(attemptId) {
    // Load the grading form for this attempt
    fetch(`/admin/quiz-attempts/${attemptId}/details`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('gradingContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('gradingModal')).show();
        })
        .catch(error => {
            console.error('Error loading grading form:', error);
            alert('Error loading grading form');
        });
}

document.getElementById('gradingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const attemptId = formData.get('attempt_id');

    fetch(`/admin/quiz-attempts/${attemptId}/grade`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error grading attempt: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error grading attempt');
    });
});
</script>
@endpush
@endsection
