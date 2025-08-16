@extends('backend.app')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Group Chat</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Apps</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Group Chat</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- Row -->
                <div class="row row-deck">
                    <div class="col-sm-12 col-md-4">
                        <div class="card overflow-scroll">
                            <div class="main-content-app pt-0">
                                <div class="main-content-left main-content-left-chat">
                                    <!-- main-chat-header -->
                                    <div class="card-body overflow-scroll border-bottom">
                                        <div class="input-group mb-2">
                                            <form action="" method="get">
                                                <div class="input-group">
                                                    <input name="keyword" type="text" id="groupKeyword"
                                                        class="form-control" placeholder="Search Group ...">
                                                    <button type="button" class="btn btn-primary text-white"
                                                        onclick="groupList();">Search</button>
                                                    <button type="button" class="btn btn-secondary text-white"
                                                        onclick="groupList();">Refresh</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- main-chat-list -->
                                    <div class="tab-content main-chat-list flex-2">
                                        <div class="tab-pane active" id="GroupList">
                                            <div class="main-chat-list tab-pane" id="groupList"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-8">
                        <div class="card">
                            <div class="main-content-app pt-0">
                                <div class="main-content-body main-content-body-chat h-100 d-none" id="GroupChatBox">
                                    <div class="main-chat-header pt-3 d-block d-sm-flex">
                                        <div class="main-img-user online" id="GroupImage"></div>
                                        <div class="main-chat-msg-name mt-2">
                                            <p class="mb-0" id="GroupName">Group</p>
                                            <small class="me-3" id="GroupMemberCount">Members</small>
                                        </div>
                                    </div>
                                    <div class="main-chat-body flex-2" id="GroupChatBody">
                                        <div class="content-inner" id="GroupChatContent"
                                            style="max-height: 500px; overflow-y: auto;"></div>
                                    </div>
                                    <div class="main-chat-footer pt-5 pb-5">
                                        <label for="GroupFile" id="GroupFileLabel" class="btn btn-primary brround"
                                            style="margin-left:20px; margin-top:8px"><i class="bi bi-image"></i></label>
                                        <input type="file" id="GroupFile" hidden accept=".jpg,.jpeg,.png,.gif">
                                        <input class="form-control" placeholder="Type your message here..." type="text"
                                            id="GroupText">
                                        <input type="text" hidden id="GroupId" />
                                        <button type="button" class="btn btn-icon btn-primary brround"
                                            onclick="sendGroupMessage($('#GroupId').val())"><i
                                                class="bi bi-send"></i></button>
                                        <button type="button" class="btn btn-icon btn-primary brround"
                                            style="margin-left:10px" onclick="groupFormClear()"><i
                                                class="bi bi-arrow-clockwise"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>

<script>
    function groupList() {
        NProgress.start();
        $.ajax({
            url: `{{ route('admin.group-chat.list') }}`,
            type: "GET",
            success: function(response) {
                NProgress.done();
                $('#groupList').empty();
                $.each(response.data.groups, function(index, value) {
                    let groupAvatar = value.cover ? value.cover : "{{ asset('default/group.jpg') }}";
                    $('#groupList').append(`
                        <a class="media new" href="javascript:void(0)" onclick="groupChat(${value.id})">
                            <div class="main-img-user online">
                                <img alt="avatar" src="${groupAvatar}">
                                ${value.is_active ? '<span class="dot-label bg-success"></span>' : '<span class="dot-label bg-danger"></span>'}
                            </div>
                            <div class="media-body">
                                <div class="media-contact-name">
                                    <span>${value.name}</span>
                                </div>
                                <span class="time">${value.last_activity_at ? dayjs(value.last_activity_at).fromNow() : 'No activity'}</span>
                            </div>
                        </a>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.log('Error fetching groups:', error);
            }
        });
    }

    groupList();

    function groupSearch() {
        NProgress.start();
        $('#groupList').empty();
        let keyword = $('#groupKeyword').val();
        $.ajax({
            url: `{{ route('admin.group-chat.search') }}?keyword=${keyword}`,
            type: "GET",
            success: function(response) {
                NProgress.done();
                $.each(response.data.groups, function(index, value) {
                    let groupAvatar = value.cover ? value.cover : "{{ asset('default/group.jpg') }}";
                    $('#groupList').append(`
                        <a class="media new" href="javascript:void(0)" onclick="groupChat(${value.id})">
                            <div class="main-img-user online">
                                <img alt="avatar" src="${groupAvatar}">
                            </div>
                            <div class="media-body">
                                <div class="media-contact-name">
                                    <span>${value.name}</span>
                                </div>
                                <span class="time">${value.created_by}</span>
                            </div>
                        </a>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.log('Error searching groups:', error);
            }
        });
    }

    function groupChat(group_id) {
        NProgress.start();
        $.ajax({
            url: `{{ route('admin.group-chat.messages', ':id') }}`.replace(':id', group_id),
            type: "GET",
            success: function(response) {
                NProgress.done();
                $('#GroupChatContent').empty();
                $('#GroupId').val(group_id);
                $('#GroupName').text(response.data.group.name);
                $('#GroupMemberCount').text(`Members: ${response.data.pagination.total || 0}`);
                $('#GroupChatBox').removeClass('d-none');

                let groupAvatar = response.data.group.cover ? response.data.group.cover : "{{ asset('default/group.jpg') }}";

                $('#GroupImage').html(`<img alt="avatar" src="${groupAvatar}">`);

                response.data.messages.forEach(chat => {
                    let senderClass = chat.sender_id == `{{auth('web')->user()->id}}` ? 'media flex-row-reverse chat-right' : 'media chat-left';
                    let avatar = chat.sender.cover ? chat.sender.cover : "{{ asset('default/profile.jpg') }}";
                    $('#GroupChatContent').append(`
                        <div class="${senderClass}">
                            <div class="main-img-user online"><img alt="avatar" src="${avatar}"></div>
                            <div class="media-body">
                                ${chat.text ? `<div class="main-msg-wrapper">${chat.text}</div>` : ''}
                                ${chat.file ? `<div class="main-msg-wrapper"><a href="${chat.file}" target="_blank"><img src="${chat.file}" style="max-width: 100px;"></a></div>` : ''}
                                <div>
                                    <span>${chat.created_at}</span>
                                </div>
                            </div>
                        </div>
                    `);
                });

                $('#GroupChatContent').scrollTop($('#GroupChatContent')[0].scrollHeight);

                // Subscribe to the group channel
                Echo.private(`chat-room.${group_id}`).listen('.GroupMessageSentEvent', function(e) {
                    toastr.success(e.data.text ?? "File Sent");
                    groupChat(group_id); // Refresh chat to show new message
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching group chat:', error);
            }
        });
    }

    $('#GroupFile').on('change', function() {
        let file = this.files[0];
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#GroupFileLabel').html(`<img src="${e.target.result}" style="width: 20px; height: 20px;"/>`);
        };
        reader.readAsDataURL(file);
    });

    function groupFormClear() {
        NProgress.start();
        $('#GroupFileLabel').html(`<i class="bi bi-image"></i>`);
        $('#GroupFile').val('');
        $('#GroupText').val('');
        NProgress.done();
        toastr.success('Form Clear');
    }

    function sendGroupMessage(group_id) {
        NProgress.start();
        let text = $('#GroupText').val() || null;
        let file = $('#GroupFile')[0].files[0] || null;
        if (text !== null || file !== null) {
            let formData = new FormData();
            if (text !== null) {
                formData.append('text', text);
            }
            if (file !== null) {
                formData.append('file', file);
            }

            $.ajax({
                url: `{{ route('admin.group-chat.send', ':id') }}`.replace(':id', group_id),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    NProgress.done();
                    $('#GroupText').val('');
                    $('#GroupFile').val('');
                    $('#GroupFileLabel').html(`<i class="bi bi-image"></i>`);
                    groupChat(group_id);
                    groupList();
                },
                error: function(xhr, status, error) {
                    console.log('Error sending group message:', error);
                }
            });
        }
    }

    setInterval(() => {
        groupList();
    }, 300000);







</script>
@endpush
