<div id="department_position_wrapper" class="{{ in_array(old('role', $selectedRole ?? ''), ['employee', 'hr', 'student'], true) ? '' : 'hidden' }}">
    <label for="department_position_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
        Position
        <span class="text-red-500 {{ in_array(old('role', $selectedRole ?? ''), ['employee', 'hr'], true) ? '' : 'hidden' }}" id="department_position_required_indicator">*</span>
    </label>
    <select name="department_position_id" id="department_position_id"
            class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm @error('department_position_id') border-red-500 @enderror">
        <option value="">Select a position</option>
    </select>
    <p id="department_position_help" class="mt-1 text-xs text-gray-500">Choose the staff position under the selected department.</p>
    @error('department_position_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const departmentPositions = @json($departmentPositionMap ?? []);
    const selectedPositionId = @json((string) old('department_position_id', $selectedPositionId ?? ''));
    const departmentSelect = document.getElementById('department_id');
    const positionSelect = document.getElementById('department_position_id');
    const positionWrapper = document.getElementById('department_position_wrapper');
    const positionRequiredIndicator = document.getElementById('department_position_required_indicator');
    const positionHelp = document.getElementById('department_position_help');
    const roleSelect = document.getElementById('role');

    if (!departmentSelect || !positionSelect || !positionWrapper) {
        return;
    }

    function isStaffRole(role) {
        return role === 'employee' || role === 'hr';
    }

    function renderPositions() {
        const departmentId = departmentSelect.value;
        const positions = departmentPositions[departmentId] || departmentPositions[String(departmentId)] || [];
        const previous = positionSelect.value;
        const role = roleSelect ? roleSelect.value : '';
        const staff = isStaffRole(role);

        positionSelect.innerHTML = '<option value="">Select a position</option>';

        positions.forEach((position) => {
            const option = document.createElement('option');
            option.value = String(position.id);
            option.textContent = position.name;
            positionSelect.appendChild(option);
        });

        const restoreId = previous || selectedPositionId;
        if (restoreId && [...positionSelect.options].some((option) => option.value === String(restoreId))) {
            positionSelect.value = String(restoreId);
        }

        const showPosition = staff || role === 'student'
            ? Boolean(departmentId)
            : false;
        positionWrapper.classList.toggle('hidden', !showPosition);
        positionSelect.required = staff && positions.length > 0;

        if (positionHelp) {
            if (!departmentId) {
                positionHelp.textContent = 'Select a department first.';
            } else if (positions.length === 0) {
                positionHelp.textContent = staff
                    ? 'This department has no positions yet. You can still save the role; add positions later under Admin → Departments.'
                    : 'This department has no positions yet.';
            } else {
                positionHelp.textContent = 'Choose the staff position under the selected department.';
            }
        }
    }

    function togglePositionRequirement() {
        const staff = roleSelect && isStaffRole(roleSelect.value);
        if (positionRequiredIndicator) {
            const departmentId = departmentSelect.value;
            const positions = departmentPositions[departmentId] || departmentPositions[String(departmentId)] || [];
            positionRequiredIndicator.classList.toggle('hidden', !(staff && positions.length > 0));
        }
        renderPositions();
    }

    departmentSelect.addEventListener('change', function () {
        positionSelect.value = '';
        renderPositions();
        togglePositionRequirement();
    });

    if (roleSelect) {
        roleSelect.addEventListener('change', togglePositionRequirement);
    }

    renderPositions();
    togglePositionRequirement();
});
</script>
