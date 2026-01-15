<!-- Task Modal -->
<div id="task-modal" 
     class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[9999]" 
     x-data="taskModal()"
     @open-task-modal.window="openModal($event.detail.task)"
     @click.away="closeModal()"
     style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900" x-text="currentTask ? 'Edit Task' : 'Create New Task'"></h3>
            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form @submit.prevent="saveTask()" id="task-form">
            <div class="space-y-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" 
                           x-model="formData.title"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea x-model="formData.description"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <!-- Due Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" 
                               x-model="formData.due_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Time</label>
                        <input type="time" 
                               x-model="formData.due_time"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Group Task: Assign Users -->
                @if($type === 'group')
                <div x-show="!currentTask || currentTask.type === 'group'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign Users</label>
                    <div class="border border-gray-300 rounded-md p-3 max-h-48 overflow-y-auto">
                        @foreach($users as $user)
                        <label class="flex items-center py-2">
                            <input type="checkbox" 
                                   :value="{{ $user->id }}"
                                   x-model="formData.assigned_users"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $user->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Attachments (if editing) -->
                <template x-if="hasAttachments()">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="attachment in currentTask.attachments" :key="attachment.id">
                            <div class="relative group">
                                <template x-if="isImage(attachment.file_type)">
                                    <img :src="attachment.file_url" 
                                         :alt="attachment.file_name"
                                         class="w-full h-32 object-cover rounded border border-gray-200">
                                </template>
                                <template x-if="!isImage(attachment.file_type)">
                                    <div class="w-full h-32 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </template>
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity rounded flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <button type="button" @click="deleteAttachment(attachment.id)" class="text-white hover:text-red-300 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600 mt-1 truncate" x-text="attachment.file_name"></p>
                            </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Upload File -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Image/File</label>
                    <input type="file" 
                           id="task-file-input"
                           @change="handleFileSelect($event)"
                           accept="image/*,.pdf,.doc,.docx"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Supported: Images, PDF, DOC, DOCX (Max 10MB)</p>
                </div>

                <!-- Comments (if editing) -->
                <template x-if="hasCurrentTask()">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                        <div class="space-y-3 max-h-48 overflow-y-auto mb-3">
                            <template x-for="comment in (currentTask && currentTask.comments ? currentTask.comments : [])" :key="comment.id">
                            <div class="p-2 bg-gray-50 rounded">
                                <div class="flex items-center mb-1">
                                    <span class="text-xs font-semibold text-gray-700" x-text="comment.user?.name"></span>
                                    <span class="ml-2 text-xs text-gray-500" x-text="new Date(comment.created_at).toLocaleString()"></span>
                                </div>
                                <p class="text-sm text-gray-600" x-text="comment.comment"></p>
                            </div>
                            </template>
                        </div>
                        <div class="flex space-x-2">
                            <input type="text" 
                                   x-model="newComment"
                                   @keyup.enter="addComment()"
                                   placeholder="Add a comment..."
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" @click="addComment()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Add
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" @click="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    <span x-text="currentTask ? 'Update Task' : 'Create Task'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
