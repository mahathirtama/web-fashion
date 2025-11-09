@extends('layout.main')

@section('title', 'Invoice Details')

@push('page-styles')
<style>
    @media print {
        body {
            background-color: #fff;
        }
        #sidebar,
        header.navbar,
        .print-button-container {
            display: none !important;
        }
        main.col-md-9 {
            width: 100% !important;
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .invoice-card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $invNumber = 'INV-' . str_pad($invoice['id'], 3, '0', STR_PAD_LEFT);
    $date = $invoice['tanggal'];

    // Due date = 7 hari dari tanggal
    $dueDate = \Carbon\Carbon::parse($date)->addDays(7)->format('Y-m-d');

    // Status otomatis
    $status = 'Paid';
    $statusClass = 'bg-success';

    // contoh rule:
    // kalau belum lewat due-date → Pending
    // kalau lewat → Overdue
    // kamu bisa custom lagi
    if (now()->lt($dueDate)) {
        $status = 'Pending';
        $statusClass = 'bg-warning text-dark';
    } else {
        $status = 'Overdue';
        $statusClass = 'bg-danger';
    }
@endphp

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Invoice #{{ $invNumber }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0 print-button-container">
        <button class="btn btn-sm btn-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i>
            Print / Save as PDF
        </button>
    </div>
</div>

<div class="card invoice-card">
    <div class="card-body p-4 p-md-5">

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-6">
                <h3 class="mb-0">FASHION ADMIN</h3>
                <small>Jl. Admin No. 123<br>Jakarta, Indonesia</small>
            </div>
            <div class="col-6 text-end">
                <h2 class="mb-0 text-uppercase">Invoice</h2>
                <p class="mb-0"><span class="fw-bold">Invoice #:</span> {{ $invNumber }}</p>
                <p class="mb-0"><span class="fw-bold">Date:</span> {{ $date }}</p>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="row border-top pt-4 mb-4">
            <div class="col-6">
                <h5 class="mb-2">Bill To:</h5>
                {{-- Placeholder karena belum ada customer --}}
                <p class="mb-0"><strong>Pelanggan Umum</strong></p>
                <p class="mb-0">-</p>
                <p class="mb-0">-</p>
            </div>

            <div class="col-6 text-end">
                <h5 class="mb-2">Payment Details:</h5>
                <p class="mb-0"><strong>Status:</strong> <span class="badge {{ $statusClass }}">{{ $status }}</span></p>
                <p class="mb-0"><strong>Due Date:</strong> {{ $dueDate }}</p>
            </div>
        </div>

        {{-- Tabel Item --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Item Description</th>
                        <th scope="col" class="text-end">Qty</th>
                        <th scope="col" class="text-end">Unit Price</th>
                        <th scope="col" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice['details'] as $d)
                        <tr>
                            <td>{{ $d['product']['name'] }}</td>
                            <td class="text-end">{{ $d['quantity'] }}</td>
                            <td class="text-end">Rp {{ number_format($d['product']['price'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($d['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Kalkulasi --}}
        <div class="row">
            <div class="col-6">
                <p class="fw-bold">Notes:</p>
                <small>Thank you for your business!</small>
            </div>

            <div class="col-6">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Subtotal:</span>
                    <span>Rp {{ number_format($invoice['subtotal'], 0, ',', '.') }}</span>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Tax ({{ $invoice['tax'] }}%):</span>
                    <span>
                        Rp {{ number_format(($invoice['subtotal'] * $invoice['tax']) / 100, 0, ',', '.') }}
                    </span>
                </div>

                <hr>

                <div class="d-flex justify-content-between fs-4">
                    <span class="fw-bold">TOTAL:</span>
                    <span class="fw-bold">Rp {{ number_format($invoice['total'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
