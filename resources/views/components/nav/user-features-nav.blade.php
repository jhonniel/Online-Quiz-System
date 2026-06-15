{{-- Default user nav items (role-based) --}}
<div class="space-y-1">
                        <p class="px-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">User Features</p>
                        <!-- Dashboard -->
                        <a href="{{ url('/dashboard') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Dashboard' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Dashboard
                            </span>
                        </a>

                        @if(auth()->user()->role === 'teacher')
                            <a href="{{ url('/teacher/students') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.teacher.students') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'My Students' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-2a3 3 0 00-3-3H10a3 3 0 00-3 3v2m10 0H7m10-9a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    My Students
                                </span>
                            </a>
                            <a href="{{ url('/teacher/pending-applications') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.teacher.pending-applications') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Student Application' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Student Application
                                </span>
                            </a>
                            <a href="{{ url('/teacher/news') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.teacher.news') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Announcements' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Announcements
                                </span>
                                @if(($teacherUnreadAnnouncementsCount ?? 0) > 0)
                                    <span class="ml-auto inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-semibold bg-red-500 text-white"
                                          :class="sidebarCollapsed ? 'hidden' : ''">
                                        {{ $teacherUnreadAnnouncementsCount > 99 ? '99+' : $teacherUnreadAnnouncementsCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ url('/teacher/excused-requests') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.teacher.excused-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Excused Requests' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Excused Requests
                                </span>
                            </a>
                            <a href="{{ url('/teacher/moa') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.teacher.moa.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Upload MOA' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V8m0 0l-3 3m3-3l3 3M5 19h14"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Upload MOA
                                </span>
                            </a>
                        @endif

                        @if(auth()->user()->canViewAssignedQuizzes())
                            <!-- Quizzes -->
                            <a href="{{ url('/quizzes') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.quizzes.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Quizzes' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Quizzes
                                </span>
                            </a>
                        @endif



                        @if(auth()->user()->role === 'technician')
                            <a href="{{ url('/technician/tickets') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.technician-tickets.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Assigned Tickets' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L6 20.75M14.25 7l3.75-3.75M7 7h.01M17 17h.01M7 17h.01M17 7h.01M12 12l0 0"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Assigned Tickets
                                </span>
                            </a>
                        @endif

                        <!-- Application (Applicant) -->
                        @if(auth()->user()->role === 'applicant')
                        <a href="{{ url('/hiring-application') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.hiring-application.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Application' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Application
                            </span>
                        </a>
                        @endif

                        <!-- DTR (Employee Only) -->
                        @if(in_array(auth()->user()->role, ['employee', 'hr', 'student']))
                        <a href="{{ url('/dtr') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'DTR' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                DTR
                            </span>
                        </a>
                        @endif

                        <!-- File Storage (Employee & Student) -->
                        @if(in_array(auth()->user()->role, ['employee', 'hr', 'student']))
                        <a href="{{ url('/files') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.files.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'File Storage' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                File Storage
                            </span>
                        </a>
                        @endif

                        @if(auth()->user()->isStaffMember())
                        <a href="{{ url('/document-requests') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-file-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Document Requests' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Document Requests
                            </span>
                        </a>
                        <a href="{{ url('/payslips') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.payslips.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Payslips' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Payslips
                            </span>
                        </a>
                        @endif

                        <!-- Leave Requests (Employee & Student) -->
                        @if(in_array(auth()->user()->role, ['employee', 'hr', 'student']))
                        <a href="{{ url('/leave-requests') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.leave-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Leave Requests' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Leave Requests
                            </span>
                        </a>
                        @endif

                        <!-- Chat -->
                        <a href="{{ url('/user-chat') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user-chat.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Chat' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Chat
                            </span>
                            <span id="unread-message-count" class="hidden ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">0</span>
                        </a>

                        <!-- Forum -->
                        <a href="{{ url('/forum') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('forum.index') || request()->routeIs('forum.show') || request()->routeIs('forum.saved') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Forum' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Forum
                            </span>
                        </a>

                        <!-- Feedback -->
                        <a href="{{ url('/feedback') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.feedback.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Feedback' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Feedback
                            </span>
                        </a>

                        @if(auth()->user()->isStaffMember() && \App\Models\User::employeeDocumentsNavEnabled())
                        @php
                            $handbookMaterials = \App\Support\EmployeeHandbookMaterial::available();
                            $policyMaterials = \App\Support\EmployeePolicyMaterial::available();
                            $handbookNavActive = request()->routeIs('user.employee-documents.show') && request()->route('type') === 'handbook'
                                || request()->routeIs('user.employee-documents.handbook-material*');
                            $policyNavActive = request()->routeIs('user.employee-documents.show') && request()->route('type') === 'policy'
                                || request()->routeIs('user.employee-documents.policy-material*');
                        @endphp
                        <div x-data="{ open: (localStorage.getItem('user-employee-documents') || 'false') === 'true' }"
                             x-init="if ({{ request()->routeIs('user.employee-documents.*') ? 'true' : 'false' }}) { open = true; }"
                             x-effect="localStorage.setItem('user-employee-documents', open)"
                             class="space-y-1">
                            <button @click="open = !open"
                                    type="button"
                                    class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 text-gray-300 hover:bg-gray-700 hover:text-white"
                                    :class="sidebarCollapsed ? 'justify-center' : ''"
                                    :title="sidebarCollapsed ? 'Documents' : ''">
                                <span class="flex items-center" :class="sidebarCollapsed ? '' : ''">
                                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Documents</span>
                                </span>
                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open, 'opacity-0 w-0 overflow-hidden': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="sidebarCollapsed ? true : open" class="space-y-1" :class="sidebarCollapsed ? '' : 'ml-4'">
                                <a href="{{ route('user.employee-documents.show', 'nda') }}"
                                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-documents.show') && request()->route('type') === 'nda' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                   :class="sidebarCollapsed ? 'justify-center' : ''"
                                   :title="sidebarCollapsed ? 'NDA' : ''">
                                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">NDA</span>
                                </a>
                                <a href="{{ route('user.employee-documents.show', 'contract') }}"
                                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-documents.show') && request()->route('type') === 'contract' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                   :class="sidebarCollapsed ? 'justify-center' : ''"
                                   :title="sidebarCollapsed ? 'Agreement' : ''">
                                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Agreement</span>
                                </a>
                                @if(count($policyMaterials) > 0)
                                <div x-data="{ policyOpen: (localStorage.getItem('user-policy-submenu') || 'false') === 'true' }"
                                     x-init="if ({{ $policyNavActive ? 'true' : 'false' }}) { policyOpen = true; }"
                                     x-effect="localStorage.setItem('user-policy-submenu', policyOpen)"
                                     class="space-y-1">
                                    <div class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $policyNavActive ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                         :class="sidebarCollapsed ? 'justify-center' : ''">
                                        <a href="{{ route('user.employee-documents.show', 'policy') }}"
                                           class="flex-1 min-w-0 text-left transition-opacity duration-300 {{ request()->routeIs('user.employee-documents.show') && request()->route('type') === 'policy' ? 'text-white' : 'text-inherit hover:text-white' }}"
                                           :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'"
                                           :title="sidebarCollapsed ? 'Policy' : ''">
                                            Policy
                                        </a>
                                        <button @click="policyOpen = !policyOpen"
                                                type="button"
                                                class="shrink-0 p-0.5 rounded hover:bg-white/10"
                                                :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : ''"
                                                aria-label="Toggle policy documents">
                                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': policyOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="sidebarCollapsed ? true : policyOpen" class="space-y-1" :class="sidebarCollapsed ? '' : 'ml-4'">
                                        @foreach($policyMaterials as $policyMaterial)
                                            <a href="{{ route('user.employee-documents.policy-material', $policyMaterial['id']) }}"
                                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-documents.policy-material') && request()->route('id') === $policyMaterial['id'] ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                               :class="sidebarCollapsed ? 'justify-center' : ''"
                                               :title="sidebarCollapsed ? $policyMaterial['name'] : ''">
                                                <span class="transition-opacity duration-300 truncate" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">{{ $policyMaterial['name'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <a href="{{ route('user.employee-documents.show', 'policy') }}"
                                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-documents.show') && request()->route('type') === 'policy' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                   :class="sidebarCollapsed ? 'justify-center' : ''"
                                   :title="sidebarCollapsed ? 'Policy' : ''">
                                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Policy</span>
                                </a>
                                @endif
                                @if(count($handbookMaterials) > 0)
                                <div x-data="{ handbookOpen: (localStorage.getItem('user-handbook-submenu') || 'false') === 'true' }"
                                     x-init="if ({{ $handbookNavActive ? 'true' : 'false' }}) { handbookOpen = true; }"
                                     x-effect="localStorage.setItem('user-handbook-submenu', handbookOpen)"
                                     class="space-y-1">
                                    <div class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $handbookNavActive ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                         :class="sidebarCollapsed ? 'justify-center' : ''">
                                        <a href="{{ route('user.employee-documents.show', 'handbook') }}"
                                           class="flex-1 min-w-0 text-left transition-opacity duration-300 {{ request()->routeIs('user.employee-documents.show') && request()->route('type') === 'handbook' ? 'text-white' : 'text-inherit hover:text-white' }}"
                                           :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'"
                                           :title="sidebarCollapsed ? 'Handbook' : ''">
                                            Handbook
                                        </a>
                                        <button @click="handbookOpen = !handbookOpen"
                                                type="button"
                                                class="shrink-0 p-0.5 rounded hover:bg-white/10"
                                                :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : ''"
                                                aria-label="Toggle handbook documents">
                                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': handbookOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="sidebarCollapsed ? true : handbookOpen" class="space-y-1" :class="sidebarCollapsed ? '' : 'ml-4'">
                                        @foreach($handbookMaterials as $handbookMaterial)
                                            <a href="{{ route('user.employee-documents.handbook-material', $handbookMaterial['id']) }}"
                                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-documents.handbook-material') && request()->route('id') === $handbookMaterial['id'] ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                               :class="sidebarCollapsed ? 'justify-center' : ''"
                                               :title="sidebarCollapsed ? $handbookMaterial['name'] : ''">
                                                <span class="transition-opacity duration-300 truncate" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">{{ $handbookMaterial['name'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <a href="{{ route('user.employee-documents.show', 'handbook') }}"
                                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.employee-documents.show') && request()->route('type') === 'handbook' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                                   :class="sidebarCollapsed ? 'justify-center' : ''"
                                   :title="sidebarCollapsed ? 'Handbook' : ''">
                                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Handbook</span>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Term of Reference (TOR) - Student and Teacher -->
                        @if(in_array(auth()->user()->role, ['student', 'teacher'], true))
                        <a href="{{ url('/tor') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.tor') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Term of Reference (TOR)' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Term of Reference (TOR)
                            </span>
                        </a>
                        @endif

                        @if(auth()->user()->isStudent())
                        <a href="{{ route('user.nda.index') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.nda.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'NDA' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                NDA
                            </span>
                        </a>
                        @endif

</div>
