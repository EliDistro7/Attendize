@extends('Shared.Layouts.Master')

@section('title')
    @lang("Order") #{{ $order->order_reference }} - @lang("Tickets")
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            @lang("Order") #{{ $order->order_reference }} - @lang("Tickets")
                        </h3>
                    </div>
                    <div class="panel-body">
                        <h4>{{ $event->title }}</h4>
                        <p>
                            <strong>@lang("Order Date"):</strong> {{ $order->created_at->format('F j, Y g:i A') }}<br>
                            <strong>@lang("Name"):</strong> {{ $order->full_name }}<br>
                            <strong>@lang("Email"):</strong> {{ $order->email }}
                        </p>

                        <hr>

                        <h4>@lang("Your Tickets")</h4>
                        
                        @if($attendees->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>@lang("Ticket Type")</th>
                                            <th>@lang("Name")</th>
                                            <th>@lang("Reference")</th>
                                            <th>@lang("Actions")</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attendees as $attendee)
                                            <tr>
                                                <td>{{ $attendee->ticket->title }}</td>
                                                <td>{{ $attendee->first_name }} {{ $attendee->last_name }}</td>
                                                <td>{{ $attendee->reference_index }}</td>
                                                <td>
                                                    <a href="{{ route('showAttendeeTicket', ['event_id' => $event->id, 'attendee_id' => $attendee->id]) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       target="_blank">
                                                        @lang("View Ticket")
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($order->ticket_pdf_path)
                                <div class="mt-3">
                                    <a href="{{ asset($order->ticket_pdf_path) }}" 
                                       class="btn btn-success" 
                                       target="_blank">
                                        <i class="ico-file-pdf"></i> @lang("Download All Tickets (PDF)")
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info">
                                @lang("No tickets found for this order.")
                            </div>
                        @endif

                        <hr>

                        <a href="{{ route('showOrderDetails', ['order_reference' => $order->order_reference]) }}" 
                           class="btn btn-default">
                            @lang("Back to Order Details")
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop