@extends('backend.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ════════════════════════════════════════════════════════════════
       RESET & BASE
    ════════════════════════════════════════════════════════════════ */
        #gc-root * {
            font-family: 'DM Sans', sans-serif;
            box-sizing: border-box;
        }

        /* ════════════════════════════════════════════════════════════════
       WRAPPER — 3 fixed columns via CSS Grid
    ════════════════════════════════════════════════════════════════ */
        #gc-root {
            display: grid;
            grid-template-columns: 280px 1fr 270px;
            height: calc(100vh - 170px);
            min-height: 560px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .08);
            background: #fff;
            box-shadow: 0 4px 32px rgba(0, 0, 0, .07);
        }

        /* ════════════════════════════════════════════════════════════════
       LEFT — GROUP SIDEBAR
    ════════════════════════════════════════════════════════════════ */
        #gc-sidebar {
            display: flex;
            flex-direction: column;
            border-right: 1px solid #f0f0f5;
            background: #fafafc;
            overflow: hidden;
            min-width: 0;
        }

        .gc-sb-header {
            padding: 18px 16px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0f0f5;
            flex-shrink: 0;
        }

        .gc-sb-header h6 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            color: #1a1a2e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .gc-sb-header h6 i {
            color: #5b6af0;
        }

        .gc-new-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #5b6af0;
            color: #fff;
            font-size: .9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s, transform .15s;
            flex-shrink: 0;
        }

        .gc-new-btn:hover {
            background: #4a58d4;
            transform: scale(1.1);
        }

        .gc-sb-search {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f5;
            flex-shrink: 0;
        }

        .gc-sb-search .search-inner {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid #e8e8f0;
            border-radius: 10px;
            padding: 7px 12px;
            transition: border-color .2s;
        }

        .gc-sb-search .search-inner:focus-within {
            border-color: #5b6af0;
        }

        .gc-sb-search input {
            border: none;
            outline: none;
            background: transparent;
            font-size: .82rem;
            width: 100%;
            color: #333;
        }

        .gc-sb-search i {
            color: #aaa;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .gc-group-list {
            flex: 1;
            overflow-y: auto;
        }

        .gc-group-list::-webkit-scrollbar {
            width: 4px;
        }

        .gc-group-list::-webkit-scrollbar-thumb {
            background: #e0e0f0;
            border-radius: 4px;
        }

        .gc-group-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f5f5fa;
            transition: background .15s;
        }

        .gc-group-item:hover {
            background: #f0f2ff;
        }

        .gc-group-item.active {
            background: #eff1ff;
            border-left: 3px solid #5b6af0;
        }

        .gc-group-item.active .gc-gi-name {
            color: #5b6af0;
        }

        .gc-gi-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #e8e8f5;
        }

        .gc-gi-body {
            flex: 1;
            min-width: 0;
        }

        .gc-gi-name {
            font-size: .84rem;
            font-weight: 600;
            color: #1a1a2e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 3px;
        }

        .gc-gi-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            color: #999;
            flex-wrap: wrap;
        }

        .gc-type-badge {
            font-size: .62rem;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .03em;
            display: inline-block;
        }

        .gc-type-badge.public {
            background: #dcfce7;
            color: #16a34a;
        }

        .gc-type-badge.private {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ════════════════════════════════════════════════════════════════
       CENTER — CHAT
    ════════════════════════════════════════════════════════════════ */
        #gc-chat {
            display: flex;
            flex-direction: column;
            background: #f7f8fc;
            overflow: hidden;
            min-width: 0;
        }

        /* Empty state */
        #gc-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 40px;
        }

        #gc-empty .empty-icon {
            width: 72px;
            height: 72px;
            background: #f0f2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #5b6af0;
            margin-bottom: 6px;
        }

        #gc-empty p {
            margin: 0;
            font-size: .88rem;
            color: #aaa;
        }

        #gc-empty .em-title {
            font-size: .95rem;
            font-weight: 700;
            color: #555;
        }

        /* Chat header */
        #gc-chat-header {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: #fff;
            border-bottom: 1px solid #f0f0f5;
            flex-shrink: 0;
            box-shadow: 0 1px 8px rgba(0, 0, 0, .04);
        }

        .gc-ch-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e8e8f5;
            flex-shrink: 0;
        }

        .gc-ch-name {
            font-weight: 700;
            font-size: .92rem;
            color: #1a1a2e;
            margin: 0 0 1px;
        }

        .gc-ch-sub {
            font-size: .74rem;
            color: #999;
        }

        .gc-ch-actions {
            margin-left: auto;
            display: flex;
            gap: 6px;
        }

        .gc-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            border: 1px solid #e8e8f0;
            background: #fff;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: .82rem;
            transition: all .15s;
        }

        .gc-icon-btn:hover {
            background: #5b6af0;
            border-color: #5b6af0;
            color: #fff;
        }

        /* Messages */
        #gc-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 20px 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        #gc-messages::-webkit-scrollbar {
            width: 4px;
        }

        #gc-messages::-webkit-scrollbar-thumb {
            background: #dde;
            border-radius: 4px;
        }

        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            max-width: 74%;
        }

        .msg-row.mine {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .msg-row:not(.mine) {
            align-self: flex-start;
        }

        .msg-av {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .msg-content {
            display: flex;
            flex-direction: column;
        }

        .msg-sender-name {
            font-size: .7rem;
            font-weight: 600;
            color: #5b6af0;
            margin-bottom: 3px;
            padding-left: 4px;
        }

        .msg-row.mine .msg-sender-name {
            display: none;
        }

        .msg-bubble {
            padding: 9px 13px;
            border-radius: 16px;
            font-size: .84rem;
            line-height: 1.55;
            word-break: break-word;
        }

        .msg-row:not(.mine) .msg-bubble {
            background: #fff;
            color: #2d2d45;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .07);
        }

        .msg-row.mine .msg-bubble {
            background: #5b6af0;
            color: #fff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 12px rgba(91, 106, 240, .3);
        }

        .msg-bubble img {
            max-width: 200px;
            border-radius: 10px;
            cursor: zoom-in;
            display: block;
            margin-top: 4px;
        }

        .msg-time {
            font-size: .67rem;
            margin-top: 4px;
            color: #bbb;
        }

        .msg-row.mine .msg-time {
            text-align: right;
            color: rgba(255, 255, 255, .6);
        }

        @keyframes msgPop {
            from {
                opacity: 0;
                transform: translateY(8px) scale(.97)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .msg-row {
            animation: msgPop .2s ease;
        }

        /* Footer */
        #gc-footer {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: #fff;
            border-top: 1px solid #f0f0f5;
            flex-shrink: 0;
        }

        .gc-file-label {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #e8e8f0;
            background: #fafafc;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
            color: #888;
            flex-shrink: 0;
            transition: all .15s;
            overflow: hidden;
        }

        .gc-file-label:hover {
            border-color: #5b6af0;
            color: #5b6af0;
        }

        .gc-file-label img {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 9px;
        }

        #gc-msg-input {
            flex: 1;
            border: 1px solid #e8e8f0;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: .84rem;
            outline: none;
            background: #fafafc;
            color: #2d2d45;
            transition: border-color .2s;
            resize: none;
            height: 40px;
        }

        #gc-msg-input:focus {
            border-color: #5b6af0;
            background: #fff;
        }

        #gc-msg-input::placeholder {
            color: #bbb;
        }

        .gc-send-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: #5b6af0;
            color: #fff;
            font-size: .9rem;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s, transform .15s;
            box-shadow: 0 3px 10px rgba(91, 106, 240, .35);
        }

        .gc-send-btn:hover {
            background: #4a58d4;
            transform: translateY(-1px);
        }

        /* ════════════════════════════════════════════════════════════════
       RIGHT — INFO PANEL
    ════════════════════════════════════════════════════════════════ */
        #gc-info {
            display: flex;
            flex-direction: column;
            border-left: 1px solid #f0f0f5;
            background: #fff;
            overflow-y: auto;
            overflow-x: hidden;
            min-width: 0;
        }

        #gc-info::-webkit-scrollbar {
            width: 4px;
        }

        #gc-info::-webkit-scrollbar-thumb {
            background: #e0e0f0;
            border-radius: 4px;
        }

        .gc-info-cover-wrap {
            position: relative;
            height: 110px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #5b6af0 0%, #818cf8 100%);
            overflow: hidden;
        }

        .gc-info-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .85;
        }

        .gc-info-avatar-wrap {
            position: absolute;
            bottom: -20px;
            left: 16px;
        }

        .gc-info-avatar {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
        }

        .gc-info-body {
            padding: 28px 16px 16px;
        }

        .gc-info-name {
            font-weight: 700;
            font-size: .95rem;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .gc-info-sub {
            font-size: .75rem;
            color: #999;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .gc-action-row {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .gc-action-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: filter .15s;
        }

        .gc-action-btn:hover {
            filter: brightness(.92);
        }

        .gc-action-btn.edit {
            background: #eff1ff;
            color: #5b6af0;
        }

        .gc-action-btn.add {
            background: #dcfce7;
            color: #16a34a;
        }

        .gc-action-btn.del {
            background: #fee2e2;
            color: #dc2626;
        }

        .gc-section-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #bbb;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .gc-member-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5fa;
        }

        .gc-member-row:last-child {
            border: none;
        }

        .gc-member-av {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            object-fit: cover;
            border: 1.5px solid #e8e8f5;
            flex-shrink: 0;
        }

        .gc-member-info {
            flex: 1;
            min-width: 0;
        }

        .gc-member-name {
            font-size: .81rem;
            font-weight: 600;
            color: #2d2d45;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gc-member-badges {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .gc-role-badge {
            font-size: .62rem;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .gc-role-badge.admin {
            background: #fee2e2;
            color: #dc2626;
        }

        .gc-role-badge.member {
            background: #eff1ff;
            color: #5b6af0;
        }

        .gc-m-action-btn {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            border: none;
            background: transparent;
            color: #bbb;
            cursor: pointer;
            font-size: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .15s;
        }

        .gc-m-action-btn:hover {
            background: #f0f2ff;
            color: #5b6af0;
        }

        /* ════════════════════════════════════════════════════════════════
       MODALS
    ════════════════════════════════════════════════════════════════ */
        .modal-content {
            border-radius: 14px;
            border: none;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
        }

        .modal-header {
            border-bottom: 1px solid #f0f0f5;
            padding: 16px 20px;
        }

        .modal-title {
            font-size: .95rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            border-top: 1px solid #f0f0f5;
            padding: 12px 20px;
        }

        /* ════════════════════════════════════════════════════════════════
       SKELETON
    ════════════════════════════════════════════════════════════════ */
        .gc-skeleton {
            background: linear-gradient(90deg, #f0f0f8 25%, #e8eaf5 50%, #f0f0f8 75%);
            background-size: 200%;
            animation: shimmer 1.4s infinite;
            border-radius: 6px;
        }

        @keyframes shimmer {
            0% {
                background-position: 200%
            }

            100% {
                background-position: -200%
            }
        }

        /* ════════════════════════════════════════════════════════════════
       RESPONSIVE
    ════════════════════════════════════════════════════════════════ */
        @media (max-width: 1100px) {
            #gc-root {
                grid-template-columns: 250px 1fr;
            }

            #gc-info {
                display: none;
                position: absolute;
                right: 0;
                top: 0;
                bottom: 0;
                width: 260px;
                z-index: 20;
                box-shadow: -4px 0 20px rgba(0, 0, 0, .1);
            }

            #gc-info.show {
                display: flex;
            }

            #gc-root {
                position: relative;
            }
        }

        @media (max-width: 768px) {
            #gc-root {
                grid-template-columns: 1fr;
                position: relative;
            }

            #gc-sidebar {
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 260px;
                z-index: 21;
                transform: translateX(-100%);
                transition: transform .25s;
                box-shadow: 4px 0 20px rgba(0, 0, 0, .1);
            }

            #gc-sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header mb-3">
                    <div>
                        <h1 class="page-title">Group Chat</h1>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Apps</a></li>
                            <li class="breadcrumb-item active">Group Chat</li>
                        </ol>
                    </div>
                </div>

                <!-- ═══════════════════════ MAIN GRID ═══════════════════════════ -->
                <div id="gc-root" class="card mb-4">

                    <!-- ─── LEFT: SIDEBAR ─── -->
                    <aside id="gc-sidebar">
                        <div class="gc-sb-header">
                            <h6><i class="bi bi-people-fill"></i> Groups</h6>
                            <button class="gc-new-btn" title="New Group" data-bs-toggle="modal"
                                data-bs-target="#createGroupModal" onclick="loadUsersIntoDropdowns()">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="gc-sb-search">
                            <div class="search-inner">
                                <i class="bi bi-search"></i>
                                <input type="text" placeholder="Search groups…" oninput="debounceSearch(this.value)">
                            </div>
                        </div>
                        <div class="gc-group-list" id="gc-group-list">
                            <div class="p-3 d-flex flex-column gap-2">
                                <div class="gc-skeleton" style="height:56px;border-radius:12px;"></div>
                                <div class="gc-skeleton" style="height:56px;border-radius:12px;"></div>
                                <div class="gc-skeleton" style="height:56px;border-radius:12px;"></div>
                            </div>
                        </div>
                    </aside>

                    <!-- ─── CENTER: CHAT ─── -->
                    <main id="gc-chat">

                        <!-- Empty state -->
                        <div id="gc-empty">
                            <div class="empty-icon"><i class="bi bi-chat-dots"></i></div>
                            <p class="em-title">Pick a group to chat</p>
                            <p>Your messages will appear here.</p>
                            <button class="btn btn-sm mt-2"
                                style="background:#5b6af0;color:#fff;border-radius:9px;font-size:.8rem;"
                                data-bs-toggle="modal" data-bs-target="#createGroupModal"
                                onclick="loadUsersIntoDropdowns()">
                                <i class="bi bi-plus-lg me-1"></i> Create Group
                            </button>
                        </div>

                        <!-- Chat Header (hidden until group selected) -->
                        <div id="gc-chat-header">
                            <img id="gc-ch-img" src="{{ asset('default/group.jpg') }}" class="gc-ch-avatar" alt="">
                            <div>
                                <p class="gc-ch-name" id="gc-ch-name">—</p>
                                <span class="gc-ch-sub" id="gc-ch-sub">—</span>
                            </div>
                            <div class="gc-ch-actions">
                                <button class="gc-icon-btn" onclick="loadMessages(currentGroupId)" title="Refresh">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button class="gc-icon-btn" onclick="toggleInfo()" title="Group Info">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                                <button class="gc-icon-btn d-md-none"
                                    onclick="document.getElementById('gc-sidebar').classList.toggle('show')">
                                    <i class="bi bi-layout-sidebar"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div id="gc-messages"
                            style="display:none;flex:1;overflow-y:auto;padding:20px;flex-direction:column;gap:6px;"></div>

                        <!-- Footer -->
                        <div id="gc-footer">
                            <label for="gc-file-input" class="gc-file-label" title="Attach image">
                                <i class="bi bi-image" id="gc-file-icon"></i>
                            </label>
                            <input type="file" id="gc-file-input" hidden accept="image/*" onchange="previewFile(this)">
                            <input type="text" id="gc-msg-input" class="form-control" placeholder="Type a message…"
                                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}">
                            <button class="gc-send-btn" onclick="sendMessage()">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </main>

                    <!-- ─── RIGHT: INFO PANEL ─── -->
                    <aside id="gc-info">
                        <div class="gc-info-cover-wrap">
                            <img id="gc-info-cover" src="{{ asset('default/group.jpg') }}" class="gc-info-cover"
                                alt="">
                            <div class="gc-info-avatar-wrap">
                                <img id="gc-info-av" src="{{ asset('default/group.jpg') }}" class="gc-info-avatar"
                                    alt="">
                            </div>
                        </div>
                        <div class="gc-info-body">
                            <p class="gc-info-name" id="gc-info-name">—</p>
                            <div class="gc-info-sub" id="gc-info-sub">—</div>

                            <div id="gc-owner-actions" class="gc-action-row d-none">
                                <button class="gc-action-btn edit" data-bs-toggle="modal"
                                    data-bs-target="#editGroupModal">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="gc-action-btn add" data-bs-toggle="modal" data-bs-target="#addMemberModal"
                                    onclick="loadUsersIntoDropdowns()">
                                    <i class="bi bi-person-plus"></i> Add
                                </button>
                                <button class="gc-action-btn del" onclick="deleteGroup()">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>

                            <hr style="border-color:#f0f0f5;margin:4px 0 14px;">
                            <p class="gc-section-title"><i class="bi bi-people"></i> Members</p>
                            <div id="gc-member-list"></div>
                        </div>
                    </aside>

                </div><!-- #gc-root -->
            </div>
        </div>
    </div>

    <!-- ════════════════════════════ MODALS ═══════════════════════════ -->

    <!-- Create Group -->
    <div class="modal fade" id="createGroupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-people-fill me-2" style="color:#5b6af0"></i>Create New Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Group Name <span
                                class="text-danger">*</span></label>
                        <input type="text" id="cg-name" class="form-control" placeholder="e.g. Marketing Team">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Cover Image</label>
                        <input type="file" id="cg-img" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Type</label>
                        <select id="cg-type" class="form-select">
                            <option value="private">🔒 Private</option>
                            <option value="public">🌐 Public</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Add Members</label>
                        <select id="cg-members" class="form-select" multiple size="5">
                            <option disabled>Loading users…</option>
                        </select>
                        <div class="form-text">Hold Ctrl / Cmd to select multiple users.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn text-white" style="background:#5b6af0;" onclick="createGroup()">
                        <i class="bi bi-check2 me-1"></i>Create Group
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Group -->
    <div class="modal fade" id="editGroupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2" style="color:#5b6af0"></i>Edit Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Group Name</label>
                        <input type="text" id="eg-name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Cover Image</label>
                        <input type="file" id="eg-img" class="form-control" accept="image/*">
                    </div>
                    <div>
                        <label class="form-label fw-semibold" style="font-size:.81rem;">Type</label>
                        <select id="eg-type" class="form-select">
                            <option value="private">🔒 Private</option>
                            <option value="public">🌐 Public</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn text-white" style="background:#5b6af0;" onclick="updateGroup()">
                        <i class="bi bi-check2 me-1"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2" style="color:#16a34a"></i>Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold" style="font-size:.81rem;">Select User</label>
                    <select id="am-user" class="form-select">
                        <option disabled selected>Select a user…</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn text-white" style="background:#16a34a;" onclick="addMember()">
                        <i class="bi bi-person-plus me-1"></i>Add Member
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Actions -->
    <div class="modal fade" id="memberActionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ma-title" style="font-size:.88rem;"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-2 p-3" id="ma-body"></div>
            </div>
        </div>
    </div>

    <!-- Image Zoom -->
    <div class="modal fade" id="imgModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background:transparent;border:none;box-shadow:none;">
                <div class="modal-body text-center p-2">
                    <img id="img-modal-src" src="" class="img-fluid rounded-3" style="max-height:85vh;"
                        alt="">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        /* ── State ─────────────────────────────────────────── */
        const ME = {{ auth('web')->id() }};
        const CSRF = "{{ csrf_token() }}";

        // Route helpers — uses url() so no named-route dependency issues
        const R = {
            list: "{{ route('admin.group-chat.list') }}",
            search: "{{ route('admin.group-chat.search') }}",
            users: "{{ route('admin.group-chat.users') }}",
            create: "{{ route('admin.group-chat.create') }}",
            msg: id => `{{ url('admin/chat/group/messages') }}/${id}`,
            send: id => `{{ url('admin/chat/group/send') }}/${id}`,
            detail: id => `{{ url('admin/chat/group/details') }}/${id}`,
            update: id => `{{ url('admin/chat/group/update') }}/${id}`,
            del: id => `{{ url('admin/chat/group/delete') }}/${id}`,
            mAdd: id => `{{ url('admin/chat/group/member/add') }}/${id}`,
            mRem: id => `{{ url('admin/chat/group/member/remove') }}/${id}`,
            mAct: id => `{{ url('admin/chat/group/member/action') }}/${id}`,
        };

        let currentGroupId = null;
        let echoChannel = null;
        let allUsers = [];
        let infoPanelOpen = window.innerWidth >= 1100;

        /* ── Boot ──────────────────────────────────────────── */
        document.addEventListener('DOMContentLoaded', () => {
            loadGroups();
            // Show info panel immediately on large screens
            if (infoPanelOpen) document.getElementById('gc-info').style.display = 'flex';
            else document.getElementById('gc-info').style.display = 'none';
        });

        /* ── Group List ─────────────────────────────────────── */
        function loadGroups() {
            $.get(R.list, res => renderGroups(res.data?.groups ?? []))
                .fail(() => toast('Failed to load groups.', 'error'));
        }

        let _st;

        function debounceSearch(kw) {
            clearTimeout(_st);
            _st = setTimeout(() =>
                $.get(R.search, {
                    keyword: kw
                }, res => renderGroups(res.data?.groups ?? [])), 320);
        }

        function renderGroups(groups) {
            const $el = $('#gc-group-list').empty();
            if (!groups.length) {
                return $el.html('<div class="text-center text-muted p-4" style="font-size:.82rem;">No groups found.</div>');
            }
            groups.forEach(g => {
                const av = g.cover ?? "{{ asset('default/group.jpg') }}";
                const type = (g.group_type ?? 'private').toLowerCase();
                $el.append(`
            <div class="gc-group-item ${currentGroupId === g.id ? 'active' : ''}" id="gi-${g.id}" onclick="openGroup(${g.id})">
                <img src="${av}" class="gc-gi-avatar" alt="">
                <div class="gc-gi-body">
                    <div class="gc-gi-name">${esc(g.name)}</div>
                    <div class="gc-gi-meta">
                        <span class="gc-type-badge ${type}">${type}</span>
                        <span>${g.members_count ?? 0} members</span>
                        ${g.last_activity_at ? '· ' + dayjs(g.last_activity_at).fromNow() : ''}
                    </div>
                </div>
            </div>`);
            });
        }

        /* ── Open Group ─────────────────────────────────────── */
        function openGroup(id) {
            currentGroupId = id;
            $('.gc-group-item').removeClass('active');
            $(`#gi-${id}`).addClass('active');

            // Show chat UI
            $('#gc-empty').hide();
            $('#gc-chat-header').css('display', 'flex');
            $('#gc-messages').css('display', 'flex');
            $('#gc-footer').css('display', 'flex');

            loadMessages(id);
            loadDetails(id);

            // Echo realtime
            if (echoChannel) Echo.leave(echoChannel);
            echoChannel = `group-chat.${id}`;
            Echo.private(echoChannel).listen('GroupMessageSentEvent', e => {
                appendMsg(e.chat);
                scrollBottom();
            });
        }

        /* ── Messages ───────────────────────────────────────── */
        function loadMessages(id) {
            const $msgs = $('#gc-messages').empty().html(
                '<div style="text-align:center;padding:30px 0;color:#ccc;font-size:.82rem;">Loading…</div>'
            );
            $.get(R.msg(id), res => {
                const {
                    group,
                    messages,
                    pagination
                } = res.data;
                $('#gc-ch-img').attr('src', group.cover ?? "{{ asset('default/group.jpg') }}");
                $('#gc-ch-name').text(group.name);
                $('#gc-ch-sub').text(`${pagination.total} messages`);
                $msgs.empty();
                if (!messages.length) {
                    $msgs.html(
                        '<div style="text-align:center;padding:40px 0;color:#ccc;font-size:.82rem;">No messages yet — say hello 👋</div>'
                        );
                } else {
                    messages.forEach(m => appendMsg(m, false));
                }
                scrollBottom();
            }).fail(() => toast('Failed to load messages.', 'error'));
        }

        function appendMsg(chat, anim = true) {
            const mine = chat.sender_id === ME;
            const av = chat.sender?.cover ?? "{{ asset('default/profile.jpg') }}";
            const name = chat.sender ?
                `${chat.sender.first_name ?? ''} ${chat.sender.last_name ?? ''}`.trim() :
                'Unknown';
            const time = chat.humanize_date || dayjs(chat.created_at).fromNow();
            const imgHtml = chat.file ?
                `<img src="${chat.file_url ?? chat.file}" onclick="zoomImg(this.src)" alt="">` :
                '';
            const txtHtml = chat.text ? `<div>${esc(chat.text)}</div>` : '';

            const el = document.createElement('div');
            el.className = `msg-row ${mine ? 'mine' : ''}`;
            if (!anim) el.style.animation = 'none';
            el.innerHTML = `
        <img src="${av}" class="msg-av" alt="">
        <div class="msg-content">
            ${!mine ? `<div class="msg-sender-name">${esc(name)}</div>` : ''}
            <div class="msg-bubble">
                ${txtHtml}${imgHtml}
                <div class="msg-time">${time}</div>
            </div>
        </div>`;
            document.getElementById('gc-messages').appendChild(el);
        }

        function scrollBottom() {
            const el = document.getElementById('gc-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }

        /* ── Send ───────────────────────────────────────────── */
        function sendMessage() {
            if (!currentGroupId) return;
            const text = $('#gc-msg-input').val().trim();
            const file = document.getElementById('gc-file-input').files[0];
            if (!text && !file) return;

            const fd = new FormData();
            if (text) fd.append('text', text);
            if (file) fd.append('file', file);

            // Optimistic render
            appendMsg({
                sender_id: ME,
                text,
                file: null,
                humanize_date: 'Just now',
                sender: {
                    first_name: 'You',
                    cover: null
                }
            });
            scrollBottom();
            $('#gc-msg-input').val('');
            resetFile();

            $.ajax({
                url: R.send(currentGroupId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                data: fd,
                processData: false,
                contentType: false,
                error: () => toast('Failed to send message.', 'error'),
            });
        }

        /* ── Group Details ──────────────────────────────────── */
        function loadDetails(id) {
            $.get(R.detail(id), res => {
                const {
                    group,
                    members
                } = res.data;
                const type = (group.group_type ?? 'private').toLowerCase();

                $('#gc-info-cover, #gc-info-av').attr('src', group.cover ?? "{{ asset('default/group.jpg') }}");
                $('#gc-info-name').text(group.name);
                $('#gc-info-sub').html(`
            <span class="gc-type-badge ${type}">${type}</span>
            <span>${group.members_count} members</span>
            ${group.last_activity_at ? '· ' + dayjs(group.last_activity_at).fromNow() : ''}
        `);
                $('#gc-ch-sub').text(`${group.members_count} members`);
                $('#eg-name').val(group.name);
                $('#eg-type').val(group.group_type ?? 'private');

                if (group.is_owner || group.is_admin) $('#gc-owner-actions').removeClass('d-none');
                else $('#gc-owner-actions').addClass('d-none');

                renderMembers(members, group.is_owner || group.is_admin);
            });
        }

        function renderMembers(members, canManage) {
            const $el = $('#gc-member-list').empty();
            members.forEach(m => {
                const av = m.avatar ?? "{{ asset('default/profile.jpg') }}";
                const rClass = m.role === 'admin' ? 'admin' : 'member';
                const flags = [
                    m.is_banned ?
                    '<i class="bi bi-slash-circle" title="Banned" style="color:#dc2626;font-size:.7rem;"></i>' :
                    '',
                    m.is_muted ?
                    '<i class="bi bi-mic-mute"   title="Muted"   style="color:#d97706;font-size:.7rem;"></i>' :
                    '',
                ].join('');
                const actionBtn = (canManage && !m.is_me) ? `
            <button class="gc-m-action-btn"
                onclick="openMemberActions(${m.id},'${esc(m.name)}','${m.role}',${m.is_muted},${m.is_banned})"
                title="Actions">
                <i class="bi bi-three-dots-vertical"></i>
            </button>` : '';

                $el.append(`
            <div class="gc-member-row" id="mr-${m.id}">
                <img src="${av}" class="gc-member-av" alt="">
                <div class="gc-member-info">
                    <div class="gc-member-name">
                        ${esc(m.name)} ${flags}
                        ${m.is_me ? '<span style="font-size:.65rem;color:#5b6af0;">(you)</span>' : ''}
                    </div>
                </div>
                <div class="gc-member-badges">
                    <span class="gc-role-badge ${rClass}">${m.role}</span>
                    ${actionBtn}
                </div>
            </div>`);
            });
        }

        /* ── Member Actions ─────────────────────────────────── */
        function openMemberActions(uid, name, role, isMuted, isBanned) {
            $('#ma-title').text(name);
            const ma = document.getElementById('memberActionModal');
            $('#ma-body').html(`
        ${role !== 'admin' ? `<button class="btn btn-light text-start w-100" onclick="doAct(${uid},'promote')"><i class="bi bi-arrow-up-circle me-2 text-primary"></i>Promote to Admin</button>` : ''}
        ${role === 'admin' ? `<button class="btn btn-light text-start w-100" onclick="doAct(${uid},'demote')"><i class="bi bi-arrow-down-circle me-2 text-warning"></i>Demote to Member</button>` : ''}
        <button class="btn btn-light text-start w-100" onclick="doAct(${uid},'${isMuted ? 'unmute' : 'mute'}')">
            <i class="bi bi-mic${isMuted ? '' : '-mute'} me-2 text-warning"></i>${isMuted ? 'Unmute' : 'Mute'} Member
        </button>
        <button class="btn btn-light text-start w-100" onclick="doAct(${uid},'${isBanned ? 'unban' : 'ban'}')">
            <i class="bi bi-slash-circle me-2 text-danger"></i>${isBanned ? 'Unban' : 'Ban'} Member
        </button>
        <hr class="my-1">
        <button class="btn text-start w-100" style="background:#fee2e2;color:#dc2626;" onclick="removeMember(${uid})">
            <i class="bi bi-person-x me-2"></i>Remove from Group
        </button>
    `);
            new bootstrap.Modal(ma).show();
        }

        function doAct(uid, action) {
            bootstrap.Modal.getInstance(document.getElementById('memberActionModal'))?.hide();
            $.ajax({
                url: R.mAct(currentGroupId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                data: {
                    user_id: uid,
                    action
                },
                success: res => {
                    toast(res.message || 'Done!', 'success');
                    loadDetails(currentGroupId);
                },
                error: xhr => toast(xhr.responseJSON?.message || 'Failed.', 'error'),
            });
        }

        function removeMember(uid) {
            bootstrap.Modal.getInstance(document.getElementById('memberActionModal'))?.hide();
            if (!confirm('Remove this member from the group?')) return;
            $.ajax({
                url: R.mRem(currentGroupId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                data: {
                    user_id: uid
                },
                success: () => {
                    toast('Member removed.', 'success');
                    $(`#mr-${uid}`).fadeOut(250);
                },
                error: xhr => toast(xhr.responseJSON?.message || 'Failed.', 'error'),
            });
        }

        /* ── Create Group ───────────────────────────────────── */
        function createGroup() {
            const name = $('#cg-name').val().trim();
            if (!name) return toast('Group name is required.', 'warning');
            const fd = new FormData();
            fd.append('name', name);
            fd.append('group_type', $('#cg-type').val());
            const img = document.getElementById('cg-img').files[0];
            if (img) fd.append('image_url', img);
            [...$('#cg-members').prop('selectedOptions')].forEach(o => fd.append('member_ids[]', o.value));

            $.ajax({
                url: R.create,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                data: fd,
                processData: false,
                contentType: false,
                success: res => {
                    bootstrap.Modal.getInstance(document.getElementById('createGroupModal')).hide();
                    toast('Group created!', 'success');
                    loadGroups();
                    openGroup(res.data.group.id);
                },
                error: xhr => toast(xhr.responseJSON?.message || 'Failed to create group.', 'error'),
            });
        }

        /* ── Update Group ───────────────────────────────────── */
        function updateGroup() {
            const fd = new FormData();
            fd.append('name', $('#eg-name').val().trim());
            fd.append('group_type', $('#eg-type').val());
            fd.append('_method', 'PATCH');
            const img = document.getElementById('eg-img').files[0];
            if (img) fd.append('image_url', img);

            $.ajax({
                url: R.update(currentGroupId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                data: fd,
                processData: false,
                contentType: false,
                success: () => {
                    bootstrap.Modal.getInstance(document.getElementById('editGroupModal')).hide();
                    toast('Group updated!', 'success');
                    loadGroups();
                    loadDetails(currentGroupId);
                },
                error: xhr => toast(xhr.responseJSON?.message || 'Failed.', 'error'),
            });
        }

        /* ── Delete Group ───────────────────────────────────── */
        function deleteGroup() {
            if (!confirm('Delete this group? All messages will be lost. This cannot be undone.')) return;
            $.ajax({
                url: R.del(currentGroupId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                success: () => {
                    toast('Group deleted.', 'success');
                    currentGroupId = null;
                    $('#gc-chat-header, #gc-footer, #gc-messages').hide();
                    $('#gc-empty').show();
                    loadGroups();
                },
                error: xhr => toast(xhr.responseJSON?.message || 'Failed.', 'error'),
            });
        }

        /* ── Add Member ─────────────────────────────────────── */
        function addMember() {
            const uid = $('#am-user').val();
            if (!uid) return toast('Please select a user.', 'warning');
            $.ajax({
                url: R.mAdd(currentGroupId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF
                },
                data: {
                    user_id: uid
                },
                success: () => {
                    bootstrap.Modal.getInstance(document.getElementById('addMemberModal')).hide();
                    toast('Member added!', 'success');
                    loadDetails(currentGroupId);
                },
                error: xhr => toast(xhr.responseJSON?.message || 'Failed.', 'error'),
            });
        }

        /* ── Users Dropdown ─────────────────────────────────── */
        function loadUsersIntoDropdowns() {
            if (allUsers.length) return;
            $.get(R.users, res => {
                allUsers = res.data?.users ?? [];
                const opts = allUsers.map(u => `<option value="${u.id}">${esc(u.name)} — ${esc(u.email)}</option>`)
                    .join('');
                $('#cg-members').html(opts);
                $('#am-user').html('<option disabled selected>Select a user…</option>' + opts);
            });
        }

        /* ── Info Panel Toggle ──────────────────────────────── */
        function toggleInfo() {
            infoPanelOpen = !infoPanelOpen;
            document.getElementById('gc-info').style.display = infoPanelOpen ? 'flex' : 'none';
        }

        /* ── File Helpers ───────────────────────────────────── */
        function previewFile(input) {
            const f = input.files[0];
            if (!f) return;
            const r = new FileReader();
            r.onload = e => document.querySelector('.gc-file-label').innerHTML = `<img src="${e.target.result}">`;
            r.readAsDataURL(f);
        }

        function resetFile() {
            document.getElementById('gc-file-input').value = '';
            document.querySelector('.gc-file-label').innerHTML = '<i class="bi bi-image" id="gc-file-icon"></i>';
        }

        function zoomImg(src) {
            document.getElementById('img-modal-src').src = src;
            new bootstrap.Modal(document.getElementById('imgModal')).show();
        }

        /* ── Util ───────────────────────────────────────────── */
        function esc(s) {
            return s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                '&quot;') : '';
        }

        function toast(msg, type = 'info') {
            if (typeof toastr !== 'undefined') toastr[type](msg);
        }

        // Auto-refresh sidebar every 5 minutes
        setInterval(loadGroups, 300_000);
    </script>
@endpush
