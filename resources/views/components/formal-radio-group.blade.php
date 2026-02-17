@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'error' => null,
    'help' => '',
    'options' => [],
    'class' => ''
])

<div class="space-y-3 {{ $class }}">
    @if($label)
        <label class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($options as $value => $option)
            <label class="relative flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-all duration-200 group">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $value }}"
                    class="sr-only peer"
                    {{ $required ? 'required' : '' }}
                    {{ old($name) == $value ? 'checked' : '' }}
                >
                <div class="flex items-center w-full">
                    @if(isset($option['icon']))
                        <div class="text-2xl mr-3 group-hover:scale-110 transition-transform duration-200">
                            {{ $option['icon'] }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition-colors duration-200">
                            {{ $option['label'] }}
                        </div>
                        @if(isset($option['description']))
                            <div class="text-sm text-gray-500 mt-1">
                                {{ $option['description'] }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Selection indicator -->
                <div class="absolute inset-0 border-2 border-transparent rounded-lg peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-all duration-200"></div>

                <!-- Check icon -->
                <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity duration-200">
                    <div class="w-5 h-5 bg-indigo-500 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </label>
        @endforeach
    </div>

    @if($help && !$error)
        <p class="text-sm text-gray-500">{{ $help }}</p>
    @endif

    @if($error)
        <p class="text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
