@extends('layouts.app')
@section('style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}" />
@endsection
@section('content')
    <div class="card mb-6">
        <div class="card-widget-separator-wrapper">
            <div class="card-body card-widget-separator">
                <div class="row gy-4 gy-sm-1">
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
                            <div>
                                <h4 class="mb-0">{{ $stats['menunggu'] }}</h4>
                                <p class="mb-0">Menunggu</p>
                            </div>
                            <div class="avatar me-sm-6">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-time-line ri-26px"></i>
                                </span>
                            </div>
                        </div>
                        <hr class="d-none d-sm-block d-lg-none me-6" />
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
                            <div>
                                <h4 class="mb-0">{{ $stats['diproses'] }}</h4>
                                <p class="mb-0">Diproses</p>
                            </div>
                            <div class="avatar me-lg-6">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-loader-4-line ri-26px"></i>
                                </span>
                            </div>
                        </div>
                        <hr class="d-none d-sm-block d-lg-none" />
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
                            <div>
                                <h4 class="mb-0">{{ $stats['selesai'] }}</h4>
                                <p class="mb-0">Selesai</p>
                            </div>
                            <div class="avatar me-sm-6">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri-check-double-line ri-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="mb-0">Rp {{ number_format($stats['total_pendapatan'], 0, ',', '.') }}</h4>
                                <p class="mb-0">Pendapatan Hari Ini</p>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ri-money-dollar-circle-line ri-26px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order List Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Daftar Pesanan</h4>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-order table">
                <thead>
                    <tr>
                        <th></th>
                        <th>No</th>
                        <th>Meja</th>
                        <th>Waktu</th>
                        <th>Atas Nama</th>
                        <th>Total</th>
                        <th>Status Pesanan</th>
                        <th>Status Pembayaran</th>
                        <th>Pembayaran</th>
                        <!-- Remove Aksi column -->
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        'use strict';

        $(function() {
            let borderColor, bodyBg, headingColor;

            if (isDarkStyle) {
                borderColor = config.colors_dark.borderColor;
                bodyBg = config.colors_dark.bodyBg;
                headingColor = config.colors_dark.headingColor;
            } else {
                borderColor = config.colors.borderColor;
                bodyBg = config.colors.bodyBg;
                headingColor = config.colors.headingColor;
            }
            var dt_order_table = $('.datatables-order');
            if (dt_order_table.length) {
                var dt_products = dt_order_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('kasir.pesanan.datatables') }}",
                    columns: [{
                            data: null,
                            className: 'control',
                            orderable: false,
                            searchable: false,
                            defaultContent: ''
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            searchable: false
                        },
                        {
                            data: 'nomor_meja',
                            name: 'nomor_meja'
                        },
                        {
                            data: 'waktu_pesan_format.display',
                            name: 'waktu_pesan'
                        },
                        {
                            data: 'atas_nama',
                            name: 'atas_nama',
                            render: function(data, type, row) {
                                var states = ['primary', 'success', 'danger', 'warning', 'info',
                                    'secondary'
                                ];
                                var stateNum = Math.floor(Math.random() * states.length);
                                var $state = states[stateNum];
                                var initials = data.match(/\b\w/g) || [];
                                initials = ((initials.shift() || '') + (initials.pop() || ''))
                                    .toUpperCase();

                                return `<div class="d-flex justify-content-start align-items-center">
                        <div class="avatar-wrapper me-3">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded-circle bg-label-${$state}">
                                    ${initials}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">${data}</span>
                            <small class="text-muted">Meja ${row.nomor_meja}</small>
                        </div>
                    </div>`;
                            }
                        },
                        {
                            data: 'total_harga',
                            render: function(data) {
                                return `<span class="text-nowrap">Rp ${parseFloat(data).toLocaleString('id-ID')}</span>`;
                            }
                        },
                        {
                            data: 'status_badge'
                        },
                        {
                            data: 'payment_status_badge' // TAMBAHKAN KOLOM INI
                        },
                        {
                            data: 'payment_badge'
                        }
                    ],
                    columnDefs: [{
                        className: 'control',
                        orderable: false,
                        searchable: false,
                        targets: 0
                    }],
                    order: [
                        [3, 'desc']
                    ],
                    dom: '<"row mx-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    language: {
                        search: '',
                        searchPlaceholder: 'Cari Pesanan',
                        lengthMenu: '_MENU_',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data'
                    },
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-label-secondary dropdown-toggle mx-3',
                        text: '<i class="ri-upload-line"></i><span class="d-none d-sm-inline-block">Export</span>',
                        buttons: [{
                                extend: 'print',
                                text: '<i class="ri-printer-line me-1"></i>Print',
                                className: 'dropdown-item'
                            },
                            {
                                extend: 'csv',
                                text: '<i class="ri-file-text-line me-1"></i>Csv',
                                className: 'dropdown-item'
                            },
                            {
                                extend: 'excel',
                                text: '<i class="ri-file-excel-line me-1"></i>Excel',
                                className: 'dropdown-item'
                            },
                            {
                                extend: 'pdf',
                                text: '<i class="ri-file-pdf-line me-1"></i>Pdf',
                                className: 'dropdown-item'
                            }
                        ]
                    }],
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal({
                                header: function(row) {
                                    return 'Detail Pesanan #' + row.data().id;
                                }
                            }),
                            type: 'column',
                            renderer: function(api, rowIdx, columns) {
                                var data = $.map(columns, function(col, i) {
                                    return col.title !== '' ?
                                        '<tr data-dt-row="' + col.rowIndex +
                                        '" data-dt-column="' + col.columnIndex + '">' +
                                        '<td>' + col.title + '</td> ' +
                                        '<td>' + col.data + '</td>' +
                                        '</tr>' : '';
                                }).join('');

                                return data ? $('<table class="table"/><tbody />').append(data) : false;
                            }
                        }
                    }
                });

                $('.dt-action-buttons').addClass('pt-0');

                setTimeout(() => {
                    $('.dataTables_filter .form-control').addClass('ms-0');
                }, 200);
            }
        });
    </script>
@endsection