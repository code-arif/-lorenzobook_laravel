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
                    <h1 class="page-title">Groups</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Groups</a></li>
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
                            <h3 class="card-title mb-0">Group List</h3>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-striped text-nowrap mb-0 table-bordered" id="datatable">
                                    <thead>
                                        <tr>
                                            <th class="bg-transparent border-bottom-0 wp-5">ID</th>
                                            <th class="bg-transparent border-bottom-0 wp-20">Name</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">Image</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">Type</th>
                                            <th class="bg-transparent border-bottom-0 wp-20">Created By</th>
                                            <th class="bg-transparent border-bottom-0 wp-15">Created At</th>
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
                    url: "{{ route('admin.group.index') }}",
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
                        orderable: true,
                        searchable: true,
                        render: function (data, type, row) {
                            let display = data && data.length > 30 ? data.substring(0, 30) + '...' : data;
                            return `<span title="${data}">${display}</span>`;
                        }
                    },
                    {
                        data: 'image_url',
                        name: 'image_url',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'group_type',
                        name: 'group_type',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
                        orderable: false,
                        searchable: false
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
</script>
@endpush
