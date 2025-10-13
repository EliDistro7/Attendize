@extends('en.Emails.Layouts.Master')

@section('message_content')

    <p>Hi there,</p>
    <p>
        Umerejeshewa pesa zako <b>{{{$attendee->event->title}}}</b>.
        <b>{{{ $refund_amount }}} imerudishwa kwa aliyelipa, utapata uthibitisho hivi punde.</b>
    </p>

    <p>
        Unaweza kuwasiliana na <b>{{{ $attendee->event->organiser->name }}}</b> moja kwa moja kwa <a href='mailto:{{{$attendee->event->organiser->email}}}'>{{{$attendee->event->organiser->email}}}</a> or by replying to this email should you require any more information.
    </p>
@stop

@section('footer')

@stop