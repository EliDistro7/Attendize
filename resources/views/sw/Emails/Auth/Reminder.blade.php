@extends('sw.Emails.Layouts.Master')

@section('message_content')
    <div>
        Hujambo,<br><br>
        Ili kubadilisha nenosiri lako, jaza fomu hii: {{ route('password.reset', ['token' => $token]) }}.
        <br><br><br>
        Asante,<br>
        Timu ya Attendize
    </div>

@stop