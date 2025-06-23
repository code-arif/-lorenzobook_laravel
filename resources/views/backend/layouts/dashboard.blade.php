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
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">
                                            {{ $all_users ?? 0 }}
                                        </h3>
                                        <p class="text-muted fs-13 mb-0">Total Users</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-primary dash ms-auto box-shadow-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
                                                <path
                                                    d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">
                                            {{ $all_groups ?? 0 }}
                                        </h3>
                                        <p class="text-muted fs-13 mb-0">Total Groups</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M4.5 11.5A.5.5 0 0 1 5 11h10a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5m-2-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m-2-4A.5.5 0 0 1 1 3h10a.5.5 0 0 1 0 1H1a.5.5 0 0 1-.5-.5" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">
                                            {{ $all_channels ?? 0 }}
                                        </h3>
                                        <p class="text-muted fs-13 mb-0">Total Channels</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-info dash ms-auto box-shadow-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path
                                                    d="M4 16s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-5.95a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                                                <path
                                                    d="M2 1a2 2 0 0 0-2 2v9.5A1.5 1.5 0 0 0 1.5 14h.653a5.4 5.4 0 0 1 1.066-2H1V3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v9h-2.219c.554.654.89 1.373 1.066 2h.653a1.5 1.5 0 0 0 1.5-1.5V3a2 2 0 0 0-2-2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <div class="card overflow-hidden">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h3 class="mb-2 fw-semibold">
                                            {{ $all_rooms ?? 0 }}
                                        </h3>
                                        <p class="text-muted fs-13 mb-0">Total Rooms</p>
                                    </div>
                                    <div class="col col-auto top-icn dash">
                                        <div class="counter-icon bg-warning dash ms-auto box-shadow-warning">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                enable-background="new 0 0 24 24" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd"
                                                    d="M6 1h6v7a.5.5 0 0 1-.757.429L9 7.083 6.757 8.43A.5.5 0 0 1 6 8z" />
                                                <path
                                                    d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2" />
                                                <path
                                                    d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ROW-1 END-->
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-6 col-xl-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-line" style="color: #4CAF50; margin-right: 5px;"></i>
                                    Monthly Summary
                                </h3>

                            </div>
                            <div class="card-body">
                                <div id="chart"></div>
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
    <script>
        /* document.addEventListener('DOMContentLoaded', function() {

            Echo.private('chat.1').listen('MessageSent', (e) => {
                console.log('Message Receiver:', e.message);
                if ($('#ReceiverId').val()) {
                    getMessage($('#ReceiverId').val());
                }
            });

            Echo.private('chat.2').listen('MessageSent', (e) => {
                console.log('Message Receiver:', e.message);
                if ($('#ReceiverId').val()) {
                    getMessage($('#ReceiverId').val());
                }
            });

            Echo.private('chat.3').listen('MessageSent', (e) => {
                console.log('Message Receiver:', e.message);
                if ($('#ReceiverId').val()) {
                    getMessage($('#ReceiverId').val());
                }
            });

        }); */
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.36.3/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('/transactions.json');
                const data = await response.json();

                const categories = Object.keys(data);
                const roomData = Object.values(data).map(v => v.rooms);
                const groupData = Object.values(data).map(v => v.groups);
                const channelData = Object.values(data).map(v => v.channels);

                const options = {
                    series: [{
                            name: "Room Joins",
                            data: roomData,
                            color: '#00E396'
                        },
                        {
                            name: "Group Creates",
                            data: groupData,
                            color: '#008FFB'
                        },
                        {
                            name: "Channel Creates",
                            data: channelData,
                            color: '#FF4560'
                        }
                    ],
                    chart: {
                        height: 400,
                        type: 'line',
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: true
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    title: {
                        text: 'Daily Joins & Creates (Last 30 Days)',
                        align: 'center',
                        style: {
                            fontSize: '20px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    },
                    xaxis: {
                        categories: categories,
                        labels: {
                            style: {
                                colors: '#777'
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        floating: true,
                        offsetY: -25,
                        offsetX: -5
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();

            } catch (error) {
                console.error('Error fetching or processing JSON data:', error);
            }
        });
    </script>
@endpush
