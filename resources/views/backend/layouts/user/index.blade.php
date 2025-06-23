@extends('backend.app', ['title' => 'Users'])

@push('styles')
<link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
@endpush

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Users</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Index</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-4 -->
            <div class="row">
                <div class="col-12 col-sm-12">
                    <div class="card product-sales-main">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">User List</h3>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-striped text-nowrap mb-0 table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="bg-transparent border-bottom-0 wp-5">ID</th>
                                            <th class="bg-transparent border-bottom-0 wp-20">Name</th>
                                            <th class="bg-transparent border-bottom-0 wp-20">Email</th>
                                            <th class="bg-transparent border-bottom-0 wp-10">Avatar</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">Join Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>
            <!-- ROW-4 END -->

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            }
        });
        if (!$.fn.DataTable.isDataTable('#datatable')) {
            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                responsive: true,
                serverSide: true,

                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                        </div>`
                },

                scroller: {
                    loadingIndicator: false
                },
                pagingType: "full_numbers",
                dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",
                ajax: {
                    url: "{{ route('admin.user.index') }}",
                    type: "GET",
                },

                columns: [
                            {
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'name',
                                name: 'name',
                                render: function (data, type, row) {
                                    let display = data && data.length > 50 ? data.substring(0, 50) + '...' : data;
                                    return `<span class="text-primary" style="font-weight: 500;" title="${data}">${display}</span>`;
                                },
                                orderable: true,
                                searchable: true
                            },
                            {
                                data: 'email',
                                name: 'email',
                                orderable: true,
                                searchable: true
                            },
                            {
                                data: 'avatar',
                                name: 'avatar',
                                orderable: false,
                                searchable: false,

                            },
                            {
                                data: 'created_at',
                                name: 'created_at',
                                orderable: true,
                                searchable: false,
                                render: function (data, type, row) {
                                    return `<span style="background: #e0f7fa; color: #00796b; padding: 4px 10px; border-radius: 12px; font-weight: 500; display: inline-block; min-width: 90px; text-align: center;">
                                        <i class='fa fa-calendar'></i> ${data}
                                    </span>`;
                                }
                            },
                        ],

            });
        }
    });


    function showDeleteConfirm(id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure you want to delete this record?',
            text: 'If you delete this, it will be gone forever. The user all data will be deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItem(id);
            }
        });
    }

    // Delete Button
    function deleteItem(id) {
        NProgress.start();
        let url = "{{ route('admin.user.destroy', ':id') }}";
        let csrfToken = '{{ csrf_token() }}';
        $.ajax({
            type: "DELETE",
            url: url.replace(':id', id),
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(resp) {
                NProgress.done();
                toastr.success(resp.message);
                $('#datatable').DataTable().ajax.reload();
            },
            error: function(error) {
                NProgress.done();
                toastr.error(error.message);
            }
        });
    }
</script>
@endpush
