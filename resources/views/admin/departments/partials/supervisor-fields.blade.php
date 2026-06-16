<div class="md:col-span-2">
    <label for="supervisor_user_id" class="block text-sm font-medium text-gray-700 mb-2">
        Assigned Immediate Supervisor
    </label>
    <select name="supervisor_user_id" id="supervisor_user_id"
            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        <option value="">— Use name only —</option>
        @foreach(($supervisorUsers ?? collect())->groupBy('role') as $role => $users)
            <optgroup label="{{ ucfirst($role) }}s">
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ (string) old('supervisor_user_id', $department->supervisor_user_id ?? '') === (string) $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    <p class="mt-1 text-sm text-gray-500">
        When assigned, this user’s name and e-signature are used on leave request letters for this department.
    </p>
    @error('supervisor_user_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="md:col-span-2">
    <label for="supervisor_name" class="block text-sm font-medium text-gray-700 mb-2">
        Immediate Supervisor Name (fallback)
    </label>
    <input type="text" name="supervisor_name" id="supervisor_name"
           value="{{ old('supervisor_name', $department->supervisor_name ?? '') }}"
           placeholder="e.g., CHARMAINE JOY ROSATACE"
           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    <p class="mt-1 text-sm text-gray-500">
        Used only when no supervisor user is assigned above.
    </p>
    @error('supervisor_name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
