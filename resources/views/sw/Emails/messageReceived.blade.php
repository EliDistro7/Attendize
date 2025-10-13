@extends('sw.Emails.Layouts.Master')

@section('message_content')

<p>Habari,</p>
<p>Umepokea message kutoka kwa <b>{{ (isset($sender_name) ? $sender_name : $event->organiser->name) }}</b> kuhusiana na event hii <b>{{ $event->title }}</b>.</p>
<p style="padding: 10px; margin:10px; border: 1px solid #f3f3f3;">
    {!! nl2br($message_content) !!}
</p>

<p>
    Unaweza kuwasiliana na <b>{{ (isset($sender_name) ? $sender_name : $event->organiser->name) }}</b> kwa email yake <a href='mailto:{{ (isset($sender_email) ? $sender_email : $event->organiser->email) }}'>{{ (isset($sender_email) ? $sender_email : $event->organiser->email) }}</a>, or by replying to this email.
</p>
@stop

@section('footer')


@stop
