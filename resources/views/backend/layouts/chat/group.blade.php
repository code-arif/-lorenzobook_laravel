@extends('backend.app')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Group Chat</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                            <li class="breadcrumb-item active">Group Chat</li>
                        </ol>
                    </div>
                </div>

                <div class="row row-deck">
                    <!-- Group List -->
                    <div class="col-sm-12 col-md-4">
                        <div class="card overflow-scroll">
                            <div class="card-body border-bottom">
                                <div class="input-group mb-2">
                                    <input type="text" id="groupKeyword" class="form-control"
                                        placeholder="Search Group ...">
                                    <button class="btn btn-primary" onclick="groupList()">Search</button>
                                    <button class="btn btn-secondary" onclick="groupList()">Refresh</button>
                                </div>
                            </div>
                            <div class="tab-content main-chat-list">
                                <div id="groupList"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Group Chat -->
                    <div class="col-sm-12 col-md-8">
                        <div class="card">
                            <div class="main-content-app pt-0">
                                <div id="GroupChatBox" class="main-content-body main-content-body-chat h-100 d-none">
                                    <div class="main-chat-header pt-3 d-flex">
                                        <div class="main-img-user online" id="GroupImage"></div>
                                        <div class="main-chat-msg-name mt-2">
                                            <p id="GroupName" class="mb-0">Group</p>
                                            <small id="GroupMemberCount">Members</small>
                                        </div>
                                    </div>
                                    <div class="main-chat-body flex-2" id="GroupChatBody">
                                        <div id="GroupChatContent" class="content-inner"
                                            style="max-height:500px; overflow-y:auto;"></div>
                                    </div>
                                    <div class="main-chat-footer pt-3 pb-3">
                                        <label for="GroupFile" id="GroupFileLabel" class="btn btn-primary brround">
                                            <i class="bi bi-image"></i>
                                        </label>
                                        <input type="file" id="GroupFile" hidden accept=".jpg,.jpeg,.png,.gif">
                                        <input type="text" id="GroupText" class="form-control"
                                            placeholder="Type your message here...">
                                        <input type="hidden" id="GroupId">
                                        <button class="btn btn-primary brround"
                                            onclick="sendGroupMessage($('#GroupId').val())">
                                            <i class="bi bi-send"></i>
                                        </button>
                                        <button class="btn btn-secondary brround" onclick="groupFormClear()">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- row -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        // List Groups
        function groupList() {
            $.get("{{ route('admin.group-chat.list') }}", function(response) {
                $('#groupList').empty();
                response.data.groups.forEach(group => {
                    let avatar = group.cover ? group.cover : "{{ asset('default/group.jpg') }}";
                    $('#groupList').append(`
                <a href="javascript:void(0)" onclick="groupChat(${group.id})" class="media new">
                    <div class="main-img-user online">
                        <img src="${avatar}" alt="avatar">
                        ${group.is_active ? '<span class="dot-label bg-success"></span>' : '<span class="dot-label bg-danger"></span>'}
                    </div>
                    <div class="media-body">
                        <div class="media-contact-name">${group.name}</div>
                        <span class="time">${group.last_activity_at ? dayjs(group.last_activity_at).fromNow() : 'No activity'}</span>
                    </div>
                </a>
            `);
                });
            });
        }

        // Open Group Chat
        function groupChat(group_id) {
            $.get(`{{ route('admin.group-chat.messages', ':id') }}`.replace(':id', group_id), function(response) {
                $('#GroupChatContent').empty();
                $('#GroupId').val(group_id);
                $('#GroupName').text(response.data.group.name);
                $('#GroupMemberCount').text(`Members: ${response.data.pagination.total || 0}`);
                $('#GroupChatBox').removeClass('d-none');
                $('#GroupImage').html(
                    `<img src="${response.data.group.cover || '{{ asset('default/group.jpg') }}'}">`);

                response.data.messages.forEach(chat => appendGroupMessage(chat));
                scrollChatToBottom();

                // Subscribe to real-time updates
                Echo.private(`group-chat.${group_id}`).listen('App\\Events\\GroupMessageSentEvent', function(e) {
                    console.log(e); // e.chat contains your chat object
                    appendGroupMessage(e.chat);
                    scrollChatToBottom();
                    toastr.success(e.chat.text ?? "File Sent");
                });


            });
        }

        // Append Group Message
        function appendGroupMessage(chat) {
            let senderClass = chat.sender_id == `{{ auth('web')->user()->id }}` ? 'media flex-row-reverse chat-right' :
                'media chat-left';
            let avatar = chat.sender.cover ? chat.sender.cover : "{{ asset('default/profile.jpg') }}";
            $('#GroupChatContent').append(`
        <div class="${senderClass}">
            <div class="main-img-user online"><img src="${avatar}" alt="avatar"></div>
            <div class="media-body">
                ${chat.text ? `<div class="main-msg-wrapper">${chat.text}</div>` : ''}
                ${chat.file ? `<div class="main-msg-wrapper"><a href="${chat.file}" target="_blank"><img src="${chat.file}" style="max-width:100px;"></a></div>` : ''}
                <div><span>${chat.humanize_date || chat.created_at}</span></div>
            </div>
        </div>
    `);
        }

        // Scroll chat to bottom
        function scrollChatToBottom() {
            let chatBox = $('#GroupChatContent')[0];
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Send Group Message
        function sendGroupMessage(group_id) {
            let text = $('#GroupText').val() || null;
            let file = $('#GroupFile')[0].files[0] || null;

            if (!text && !file) return;

            let formData = new FormData();
            if (text) formData.append('text', text);
            if (file) formData.append('file', file);

            $.ajax({
                url: `{{ route('admin.group-chat.send', ':id') }}`.replace(':id', group_id),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    $('#GroupText').val('');
                    $('#GroupFile').val('');
                    $('#GroupFileLabel').html(`<i class="bi bi-image"></i>`);
                }
            });
        }

        // Handle file preview
        $('#GroupFile').on('change', function() {
            let reader = new FileReader();
            reader.onload = e => $('#GroupFileLabel').html(
                `<img src="${e.target.result}" style="width:20px;height:20px;">`);
            reader.readAsDataURL(this.files[0]);
        });

        // Clear form
        function groupFormClear() {
            $('#GroupFileLabel').html(`<i class="bi bi-image"></i>`);
            $('#GroupFile').val('');
            $('#GroupText').val('');
            toastr.success('Form cleared');
        }

        // Auto refresh group list every 5 min
        setInterval(groupList, 300000);

        groupList();
    </script>
@endpush
