@extends('layouts.admin')

@section('page-title', 'Contact Message Details')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ route('contact-messages.index') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Contact Messages</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Message Details</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Contact Message</h1>
                    <p class="text-indigo-100 text-sm">View and respond to contact message</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('contact-messages.index') }}"
                   class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline">Back to Messages</span>
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-sm flex-shrink-0" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Message Details -->
    <div class="flex-1 overflow-y-auto space-y-3 sm:space-y-4">
        <div class="bg-gray-50 rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Message Details</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($contactMessage->isNew()) bg-red-100 text-red-800
                    @elseif($contactMessage->isRead()) bg-yellow-100 text-yellow-800
                    @elseif($contactMessage->isReplied()) bg-green-100 text-green-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst($contactMessage->status) }}
                </span>
            </div>

            <dl class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs sm:text-sm font-medium text-gray-500">From</dt>
                    <dd class="text-sm sm:text-base text-gray-900">{{ $contactMessage->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs sm:text-sm font-medium text-gray-500">Email</dt>
                    <dd class="text-sm sm:text-base text-gray-900">
                        <a href="mailto:{{ $contactMessage->email }}" class="text-indigo-600 hover:text-indigo-500 break-all">
                            {{ $contactMessage->email }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs sm:text-sm font-medium text-gray-500">Subject</dt>
                    <dd class="text-sm sm:text-base text-gray-900">{{ $contactMessage->subject }}</dd>
                </div>
                <div>
                    <dt class="text-xs sm:text-sm font-medium text-gray-500">Date</dt>
                    <dd class="text-sm sm:text-base text-gray-900">{{ $contactMessage->created_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($contactMessage->read_at)
                    <div>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500">Read by</dt>
                        <dd class="text-sm sm:text-base text-gray-900">{{ $contactMessage->readBy->name ?? 'Unknown' }}</dd>
                    </div>
                @endif
                @if($contactMessage->replied_at)
                    <div>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500">Replied by</dt>
                        <dd class="text-sm sm:text-base text-gray-900">{{ $contactMessage->repliedBy->name ?? 'Unknown' }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Message</h3>
            <div class="prose max-w-none">
                <p class="text-sm sm:text-base text-gray-700 whitespace-pre-wrap">{{ $contactMessage->message }}</p>
            </div>
        </div>

        <!-- Admin Reply Section -->
        @if($contactMessage->admin_reply)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-medium text-green-900 mb-3 sm:mb-4">Admin Reply</h3>
                <div class="prose max-w-none">
                    <p class="text-sm sm:text-base text-green-700 whitespace-pre-wrap">{{ $contactMessage->admin_reply }}</p>
                </div>
                <p class="text-xs sm:text-sm text-green-600 mt-2">
                    Replied on {{ $contactMessage->replied_at->format('M d, Y H:i') }}
                    by {{ $contactMessage->repliedBy->name ?? 'Unknown' }}
                </p>
            </div>
        @else
            <!-- Reply Form -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Send Reply</h3>

                <form action="{{ route('contact-messages.reply', $contactMessage) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="admin_reply" class="block text-sm font-medium text-gray-700">Reply Message</label>
                        <textarea name="admin_reply" id="admin_reply" rows="4" required
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                                  placeholder="Type your reply here..."></textarea>
                        @error('admin_reply')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 pt-4 border-t border-gray-200">
            <div class="flex space-x-3">
                @if(!$contactMessage->isClosed())
                    <form method="POST" action="{{ route('contact-messages.close', $contactMessage) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Close Message
                        </button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('contact-messages.destroy', $contactMessage) }}" class="inline"
                  onsubmit="return confirmMessageAction('delete', this)">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete Message
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmMessageAction(action, button) {
        event.preventDefault();

        if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
            button.closest('form').submit();
        }

        return false;
    }
</script>
@endsection
