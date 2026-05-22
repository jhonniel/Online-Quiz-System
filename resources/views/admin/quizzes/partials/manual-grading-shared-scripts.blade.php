<script>
window.ManualGrading = {
    initForms(root) {
        const scope = root || document;
        scope.querySelectorAll('input[name="is_correct"]').forEach(radio => {
            if (radio.dataset.mgBound) return;
            radio.dataset.mgBound = '1';
            radio.addEventListener('change', function() {
                const form = this.closest('form');
                const pointsInput = form.querySelector('input[name="points_earned"]');
                const maxPoints = parseInt(pointsInput.getAttribute('max'), 10);
                pointsInput.value = this.value === '1' ? maxPoints : '0';
            });
        });

        scope.querySelectorAll('.grade-form').forEach(form => {
            if (form.dataset.mgFormBound) return;
            form.dataset.mgFormBound = '1';

            const pointsInput = form.querySelector('input[name="points_earned"]');
            const isCorrectRadio = form.querySelector('input[name="is_correct"]:checked');
            if (isCorrectRadio && isCorrectRadio.value === '0') {
                pointsInput.value = '0';
            }
            const allRadios = form.querySelectorAll('input[name="is_correct"]');
            if (!form.querySelector('input[name="is_correct"]:checked') && allRadios.length > 0) {
                allRadios[0].checked = true;
                pointsInput.value = '0';
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (this.dataset.submitting === 'true') return;
                this.dataset.submitting = 'true';

                const attemptId = this.dataset.attemptId;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Grading…</span>';

                let isCorrectRadio = this.querySelector('input[name="is_correct"]:checked');
                if (!formData.get('is_correct') && isCorrectRadio) {
                    formData.set('is_correct', isCorrectRadio.value);
                } else if (!formData.get('is_correct')) {
                    const firstRadio = this.querySelector('input[name="is_correct"]');
                    if (firstRadio) {
                        firstRadio.checked = true;
                        formData.set('is_correct', firstRadio.value);
                    } else {
                        alert('Please select Correct or Incorrect before submitting.');
                        this.dataset.submitting = 'false';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        return;
                    }
                }

                const isCorrectValue = formData.get('is_correct');
                const pointsValue = formData.get('points_earned');
                const maxPoints = parseInt(this.querySelector('input[name="points_earned"]').getAttribute('max'), 10);
                if (isCorrectValue === '1' && pointsValue === '0') {
                    formData.set('points_earned', String(maxPoints));
                } else if (isCorrectValue === '0' && pointsValue !== '0') {
                    formData.set('points_earned', '0');
                }

                fetch(`/admin/quiz-attempts/${attemptId}/grade`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.success(data.message);
                        }
                        const formContainer = document.getElementById(`attempt-${attemptId}`);
                        if (formContainer) {
                            const panel = formContainer.closest('[data-quiz-grading-panel]');
                            formContainer.style.opacity = '0';
                            formContainer.style.transform = 'translateY(-4px)';
                            formContainer.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                            setTimeout(() => {
                                formContainer.remove();
                                if (panel) {
                                    const remaining = panel.querySelectorAll('[data-grading-attempts] .grade-form').length;
                                    ManualGrading.decrementPendingBadges(panel);
                                    if (remaining === 0) {
                                        window.dispatchEvent(new CustomEvent('manual-grading-quiz-complete'));
                                    }
                                }
                            }, 260);
                        }
                        if (document.querySelectorAll('.grade-form').length === 0) {
                            window.location.reload();
                        }
                    } else {
                        if (typeof ToastNotification !== 'undefined') {
                            ToastNotification.error(data.message || 'An error occurred while grading.');
                        }
                        this.dataset.submitting = 'false';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(error.message || 'An error occurred while grading.');
                    }
                    this.dataset.submitting = 'false';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        });
    },

    decrementPendingBadges(panel) {
        const userId = panel.getAttribute('data-user-id');
        const quizId = panel.getAttribute('data-quiz-id');
        [document.querySelector('[data-pending-badge="student-' + userId + '"]'),
         document.querySelector('[data-pending-badge="quiz-' + userId + '-' + quizId + '"]')].forEach(function(badge) {
            if (!badge) return;
            const n = parseInt(badge.textContent.trim(), 10);
            if (!isNaN(n) && n > 0) badge.textContent = String(n - 1);
        });
    },
};

function mgNavigationMixin(viewMode) {
    return {
        viewMode,
        pageReady: false,
        workspaceLoading: false,
        workspaceSkeleton: 'quiz-grid',

        init() {
            this.$nextTick(() => {
                this.pageReady = true;
                if (this.$refs.workspace) {
                    window.ManualGrading.initForms(this.$refs.workspace);
                }
            });
        },

        resolveWorkspaceSkeleton(params) {
            const hasUser = params.has('user_id');
            const hasQuiz = params.has('quiz_id');
            if (hasUser && hasQuiz) {
                return 'grading';
            }
            return 'quiz-grid';
        },

        async loadWorkspace(params) {
            this.workspaceSkeleton = this.resolveWorkspaceSkeleton(params);
            this.workspaceLoading = true;
            try {
                const res = await fetch(this.mgUrl(params), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) throw new Error('Failed to load');
                const data = await res.json();
                this.selectedUserId = data.selectedUserId;
                this.selectedQuizId = data.selectedQuizId;
                if (data.secondaryListKey === 'quizzes') {
                    this.quizzes = data.secondaryList;
                } else if (data.secondaryListKey === 'students') {
                    this.students = data.secondaryList;
                }
                if (this.$refs.workspace) {
                    this.$refs.workspace.innerHTML = data.workspaceHtml;
                    window.ManualGrading.initForms(this.$refs.workspace);
                }
                const url = this.mgUrl(params);
                window.history.replaceState(null, '', url);
            } catch (e) {
                console.error(e);
                window.location.href = this.mgUrl(params);
            } finally {
                this.workspaceLoading = false;
            }
        },

        handleWorkspaceClick(event) {
            const el = event.target.closest('[data-mg-action]');
            if (!el || this.workspaceLoading) return;
            const action = el.getAttribute('data-mg-action');
            if (action === 'select-quiz') {
                this.selectQuiz(parseInt(el.getAttribute('data-quiz-id'), 10));
            } else if (action === 'select-student') {
                this.selectStudent(parseInt(el.getAttribute('data-user-id'), 10));
            } else if (action === 'back-to-students') {
                this.backToStudents();
            } else if (action === 'back-to-quizzes') {
                this.backToQuizzes();
            }
        },
    };
}

document.addEventListener('DOMContentLoaded', function() {
    window.ManualGrading.initForms(document);
});
</script>
