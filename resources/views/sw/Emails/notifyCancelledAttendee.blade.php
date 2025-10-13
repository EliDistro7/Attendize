@extends('sw.Emails.Layouts.Master')

@section('message_content')

<p>Hi there,</p>
<p>
    iketi yako kwa ajili ya tukio <b>{{{$attendee->event->title}}}</b> imeondolewa.
</p>

<p>
    Unaweza kuwasiliana na <b>{{{$attendee->event->organiser->name}}}</b> kwa barua pepe kuhusu hilo <a href='mailto:{{{$attendee->event->organiser->email}}}'>{{{$attendee->event->organiser->email}}}</a> or by replying to this email should you require any more information.
</p>
@stop

@section('footer')

@stop
