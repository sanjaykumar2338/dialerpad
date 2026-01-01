@extends('admin.layout')

@section('page-title', 'Dashboard')

@section('content')
    <h2>Dashboard</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <div style="padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            <h3>Active Cards</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ $active_cards }}</p>
        </div>
        <div style="padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            <h3>Expired Cards</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ $expired_cards }}</p>
        </div>
        <div style="padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            <h3>Total Minutes Sold</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ number_format($total_minutes_sold) }}</p>
        </div>
        <div style="padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            <h3>Total Minutes Used</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ number_format($total_minutes_used) }}</p>
        </div>
        <div style="padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px;">
            <h3>Total Calls</h3>
            <p style="font-size: 2rem; font-weight: bold;">{{ number_format($total_calls) }}</p>
        </div>
    </div>
@endsection
<div id="sip-status">NOT REGISTERED</div>
<button id="call-btn" disabled>Call</button>
